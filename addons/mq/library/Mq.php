<?php
/**
 * Mq.php
 * 易聚合支付系统
 * =========================================================

 * ----------------------------------------------
 *
 *
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-06-09
 */

namespace addons\mq\library;

use addons\mq\model\MqAccount;
use addons\mq\model\MqOrder;
use think\Db;

class Mq
{
    /**
     * 创建订单，返回支付页面的网址
     */
    public static function create($orderno, $price, $type)
    {

        $config = get_addon_config('mq');

        $time = time() - intval($config['orderValidity']) * 60;

        MqOrder::update([
            'status' => '2'
        ], [
            'status' => '0',
            'createtime' => ['LT', $time],
        ]);


        $paymax = $type == 'wechat' ? $config['wxpaymax'] : $config['alipaymax'];

        $paymax = intval($paymax);


        $data = [];

        //查询未过期未支付的同等金额是否存在
        $mqOrderList = MqOrder::where([
            'price' => $price,
            'createtime' => ['EGT', $time],
            'type' => $type,
            'status' => ['EQ', '0'],
        ])->select();

        //如果为空可以直接使用
        if (empty($mqOrderList)) {
            $data = [
                'orderno' => $orderno,
                'price' => $price,
                'realprice' => $price,
                'type' => $type
            ];
        } else {
            //查询其他金额
            $otherPrice = [];
            for ($i = 1; $i <= $paymax; $i++) {
                array_push($otherPrice, bcsub($price, $i * 0.01));
            }
            //查询已经用到的金额列表
            $hasUsePrice = MqOrder::where([
                'realprice' => ['IN', $otherPrice],
                'createtime' => ['EGT', $time],
                'type' => $type,
                'status' => ['EQ', '0'],       //正在使用当中的金额
            ])->column('realprice');
            //计算两个数组的差集
            $canUserPrice = array_values(array_diff($otherPrice, $hasUsePrice));
            //然后随机选择一个金额
            if (empty($canUserPrice)) {
                exception('系统火爆，请过1-3分钟后下单!');
            }
            $realPrice = $canUserPrice[random_int(0, count($canUserPrice) - 1)];
            $data = [
                'orderno' => $orderno,
                'price' => $price,
                'realprice' => $realPrice,
                'type' => $type
            ];
        }

        //随机取出一条
        $account = MqAccount::getAccount($type);
        if (empty($account)) {
            exception('暂无收款账户!');
        }
        //增加写入数据
        $data['mq_account_id'] = $account['id'];
        MqOrder::create($data);
        //返回支付页面的url地址
        return addon_url('mq/index/show', [
            'orderno' => $orderno
        ], '', true);
    }

    /**
     *
     * 根据金额找到订单号
     * @param $realprice
     * @param $type
     */
    public static function findOrderno($realprice, $type)
    {
        $config = get_addon_config('mq');

        $time = time() - intval($config['orderValidity']) * 60;

        $result = MqOrder::where([
            'realprice' => $realprice,            //实际付款金额
            'createtime' => ['EGT', $time],     //有效期
            'type' => $type
        ])->field('orderno')->value('orderno');


        return is_null($result) ? false : $result;


    }


    /**
     * 返回域名列表
     */
    public static function getDomain()
    {
        $config = get_addon_config('mq');

        $domains = explode('\r\n', $config['domain']);


        return $domains[array_rand($domains)];

    }


    public static function orderFinish($orderNo)
    {

        $mqOrderModel = MqOrder::get(['orderno' => $orderNo]);
        if (is_null($mqOrderModel)) {
            return false;
        }
        $mqAccountModel = MqAccount::get($mqOrderModel->mq_account_id);
        if (is_null($mqAccountModel)) {
            return false;
        }
        Db::startTrans();
        try {
            $mqOrderModel->status = '1';
            $mqOrderModel->save();
            //改变订单每日额度
            $mqAccountModel->todaymoney = $mqAccountModel->todaymoney + $mqOrderModel['realprice'];
            $mqAccountModel->save();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }

        return true;

    }

}