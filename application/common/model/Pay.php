<?php

namespace app\common\model;

use think\Db;
use think\Model;
use think\Queue;

/**
 * Pay.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-11
 */
class Pay extends Model
{


    // 表名
    protected $name = 'pay';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'style_text',
        'status_text',
        'daifustatus_text',
        'notify_status_text',
        'createtime_text'
    ];


    public function getCreatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['createtime']) ? $data['createtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getStyleList()
    {
        return ['0' => '后台申请', '1' => 'API提交'];
    }

    public function getStatusList()
    {
        return ['0' => '待处理', '1' => '已支付', '2' => '冻结', '3' => '已取消'];
    }

    public function getDaifustatusList()
    {
        return ['0' => '未提交', '1' => '已提交', '2' => '已失败', '3' => '已成功'];
    }

    public function getNotifyStatusList()
    {
        return ['0' => '未通知', '1' => '通知失败', '2' => '通知成功'];
    }


    public function getStyleTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['style']) ? $data['style'] : '');
        $list = $this->getStyleList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDaifustatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['daifustatus']) ? $data['daifustatus'] : '');
        $list = $this->getDaifustatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getNotifyStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['notify_status']) ? $data['notify_status'] : '');
        $list = $this->getNotifyStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function getReqInfoAttr($value)
    {

        $req = [];
        parse_str($value, $req);

        return $req;
    }

    /**
     * 获取订单号
     * @return int
     * @throws \think\exception\DbException
     */
    public static function createOrderNo()
    {
        $order_sn = create_orderno();
        if (!is_null(self::get(['orderno' => $order_sn]))) {
            $order_sn = self::createOrderNo();
        }
        return $order_sn;
    }


    /**
     * 代付接口提交
     * @param $payId
     * @param $dfAccountId
     */
    public static function dfSubmit($payId, $dfAccountId, $isOrderno=false)
    {

        if ($isOrderno) {
            $payModel = self::get([
                'orderno' => $payId,
                'status' => '0',
                'daifustatus' => ['in', '0,2']
            ]);

            if (is_null($payModel)) {
                exception('用户支付信息不存在或已支付！');
            }
            $payId = $payModel['id'];

        } else {
            $payModel = self::get([
                'id' => $payId,
                'status' => '0',
                'daifustatus' => ['in', '0,2']
            ]);
        }


        if (is_null($payModel)) {
            exception('用户支付信息不存在或已支付！');
        }
        $accountModel = ApiAccount::get([
            'id' => $dfAccountId,
            'ifrepay' => '1'
        ]);
        if (is_null($accountModel)) {
            exception('代付账户未开启或不存在！');
        }
        //查看是不是有等待支付的订单
        $waitedCount = PayOrder::where([
            'pay_id' => $payId,
            'status' => ['in', '0,1']
        ])->count();

        if ($waitedCount > 0) {
            exception('代付状态已成功或者正在代付，请不要重复打款');
        }

        Db::startTrans();

        try {
            //生成代付订单号
            $ddh = PayOrder::createOrderNo();
            //写入代付订单表
            $data = array(
                'orderno' => $ddh,
                'createtime' => time(),
                'pay_id' => $payId,
                'api_account_id' => $dfAccountId,
                'status' => '0'
            );
            PayOrder::create($data);

            //调用支付接口
            $code = $accountModel->upstream->code;

            $domain = config('site.gateway');   //返回的域名
            //获取域名
            if ($accountModel['domain']) {
                $domain = $accountModel['domain'];
            }
            //调用代付接口的参数
            $params = array(
                'orderno' => $ddh,
                'payData' => $payModel->toArray(),
                'params' => $accountModel['params'],
                'notifyUrl' => $domain . "/Pay/repaynotify/code/" . $code
            );
            $result = loadApi($code)->repay($params);

            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            exception('系统异常:' . $e->getMessage());
        }
        return $result;
    }


    /**
     * $data=array(
     * 'orderno'=>$val['orderid'], 订单号
     * 'money'=>$val['amount'],金额
     * 'outorderno'=>$val['transaction'],外部订单
     * 'msg'=>$val['returnmsg'],返回说明
     * 'status'=>$val['returncode']=='00'?1:0 状态 0失败 1成功 2状态不变*
     * 更新代付订单状态
     * @param $params
     */
    public static function changePayStatus($params)
    {

        $payOrderModel = PayOrder::get([
            'orderno' => $params['orderno']
        ]);

        if (is_null($payOrderModel)) {
            return [0, '未找到代付订单。'];
        }

        //检查金额
        $payModel = self::get($payOrderModel['pay_id']);
        if (is_null($payModel)) {
            return [0, '未找到代付订单。'];
        }

        if ($payModel['money'] != $params['money']) {
            return [0, '代付金额与提交金额不一致。'];
        }
        //如果订单已被冻结或者取消
        if ($payModel['status'] > 1) {
            return [0, '订单已被管理员处理'];
        }

        $status = 2; //代付进行中
        $return = '处理完成.';

        //只有申请中的订单可以操作
        if ($payModel['status'] == '0') {

            //代付已成功
            if ($params['status'] == '1') {

                $return = '代付已支付';
                $status = 1; //代付成功

                $payData = [
                    'status' => '1',
                    'daifustatus' => '3'
                ];
                $payOrderData = [
                    'status' => '1',
                    'outorderno' => $params['outorderno'],
                    'paytime' => time(),
                    'outdesc' => $params['msg']
                ];
            }

            //代付已失败
            if ($params['status'] == '0') {
                $return = '当前代付提交返回状态失败';
                $payData = [
                    'daifustatus' => '2'
                ];
                $payOrderData = [
                    'status' => '2',
                    'outorderno' => $params['outorderno'],
                    'paytime' => time(),
                    'outdesc' => $params['msg']
                ];
            }
            //代付申请中
            if ($params['status'] == '2') {
                $return = '当前代付提交返回状态代付中';
                $payData = [
                    'daifustatus' => '1'
                ];
                $payOrderData = [
                    'status' => '0',
                    'outorderno' => $params['outorderno'],
                    'paytime' => time(),
                    'outdesc' => $params['msg']
                ];
            }

            //开启事务提交
            Db::startTrans();
            try {
                $payModel->save($payData);
                $payOrderModel->save($payOrderData);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return [0, $e->getMessage()];
            }

            //到这里可以通知客户了
            if ($payModel['style'] == '1' && !empty($payModel['req_info']['notifyUrl'])) {
                Queue::push('app\common\job\RepayNotify',['pay_id'=>$payModel->id]);
            }

            return [1, $return];
        } else {
            return [0, '该订单已支付成功或已被管理员处理'];
        }

    }


}