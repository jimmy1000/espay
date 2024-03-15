<?php
/**
 * Leiting.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-04-29
 */

namespace app\common\api;

use think\Log;

class Leiting extends Base
{


    /**
     *
     * $params = [
     * 'config' => '',                                  //配置参数
     * 'merId' => '',                                   //商户号
     * 'sys_orderno' => '',                             //订单号
     * 'total_money' => '',                             //订单金额
     * 'channel' => '',                                //通道代码
     * 'desc' => '',                                   //简单描述
     * 'user_id' =>'',                                 //快捷模式必须
     * 'ip' => '',                                     //ip地址
     * 'domain' => '',                                 //地址信息
     * 'notify_url'=>'',                               //通知地址
     * 'return_url'=>'',                               //返回地址
     * ];
     * @param $params
     * @return mixed|void
     */
    public function pay($params)
    {

        if ($params['channel'] == 'zfbsm') {
            $url = 'http://www.baidu.com';
            return [1, $this->qrcode($url, 'zfbsm')];
        }

        $text = '<h1>66666</h1>';

        return [1,$this->formUrl($params['sys_orderno'],$text)];
        //返回支付页面的地址
//        return [1, 'http://www.baidu.com'];
    }

    /**
     * 通知方法
     */
    public function notify()
    {

        $orderno = '351996992861241344';
        $amount = '100';
        $config = $this->getOrderConfig($orderno);
        //更新订单状态
        $params = [
            'orderno' => $orderno,    //系统订单号
            'up_orderno' => '888888',   //上游单号
            'amount' => $amount       //金额
        ];

        //分布式锁防止重复更改余额
        $redislock = redisLocker();
        $resource = $redislock->lock('pay.'.$orderno, 3000);   //单位毫秒
        if($resource){
            Log::write('进入修改数据','error');
            try {
                $this->orderFinish($params);
            } catch (\Exception $e) {
            } finally {
                $redislock->unlock(['resource' => 'pay.'.$orderno, 'token' => $resource['token']]);
            }
        }else{
            Log::write('获取锁失败','error');
        }
        
        return 'success';
    }

    /**
     * 同步跳转方法
     */
    public function backurl($orderno = '')
    {

        $orderno = request()->param('orderno');
        //我直接调用回调方法完成订单
//        $this->notify();
        $result = parent::backurl($orderno);
        return $result;
    }


}