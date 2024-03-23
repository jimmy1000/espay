<?php
namespace app\common\api;

use app\common\model\Order;
use fast\Http;
use think\Log;

class Jinyi extends Base
{


    public function pay($params)
    {

        $config = $params['config'];
        $upstream_config= $params['upstream_config'];
        $jinyi_mchNo = $config['mch_id'];
        $jinyi_key = $config['key'];
        $request_data = array();
        $request_data['mch_id'] = $jinyi_mchNo;//商户号
        $request_data['out_trade_no'] = $params['sys_orderno'];//商户订单号
        $request_data['trade_type'] = $params['channel'];//支付产品ID，具体请与平台管理联系
        $request_data['amount'] = $params['total_money'];//支付金额
        $request_data['mch_create_ip'] = $params['ip'];//客户端ip
        $request_data['notify_url'] = $params['notify_url'];//回调地址
        $request_data['return_url'] = $params['return_url'];//同步回调地址
        $request_data['sign']=$this->_xsign($request_data, $jinyi_key);//执行签名

//
//
//        以前的参数 留着做对比
//        $request_data['attach'] = $params['sys_orderno'];//非必填项
//        $request_data['member_sn'] = $params['sys_orderno'];//订单编号
//        $request_data['order_amount'] = $params['total_money'];
//        $request_data['payment_code'] = 'unionpay';
//        $request_data['member_id'] = $config['id'];
//        $request_data['sign'] = $this->_sign($request_data, $config['key']);//执行签名
//        $request_data['callback_url'] = $params['notify_url'];//异步回调地址,不参与签名
        $pay_url = $params['pay_url'];//下单接口
        $resp = Http::post($pay_url, $request_data);
        $result = json_decode($resp, true);
        if ($result['code'] == 0) {
            $pay_url = $result['data']['payUrl'];//支付URL，直接跳转或者转为二维码
            return [1,$pay_url];
        }
        return [0,$result['msg']];

    }


    public function notify()
    {

        Log::write('金蚁支付回调信息:' . http_build_query($_REQUEST), 'CHANNEL');
        $response_data = array();
        $response_data['mch_id'] = $_REQUEST['mch_id']; //商户号
        $response_data['status'] = $_REQUEST['status'];   //状态1成功0失败
        $response_data['out_trade_no'] = $_REQUEST['out_trade_no']; //返回商户订单
        $response_data['sys_order_no'] = $_REQUEST['sys_order_no']; //系统订单号
        $response_data['amount'] = $_REQUEST['amount'];//支付金额
        $response_data['trade_type'] = $_REQUEST['trade_type'];//支付方式
        $response_data['pay_time'] = $_REQUEST['pay_time'];//支付时间
        $response_sign = $_REQUEST['sign'];
        $ddh = $response_data['out_trade_no']; //获取商户传入的订单号
        $config = $this->getOrderConfig($ddh);//获取订单上游配置
        $key = $config['key']; //秘钥，请获取最新秘钥
        $get_sign = $this->_xsign($response_data,$key);//执行签名

        if($response_sign==$get_sign) {
            $orderModel = Order::get(['sys_orderno' => $ddh]);

            //同一时刻 同一用户只能处理一个
            $redislock = redisLocker();
            $resource = $redislock->lock('pay.' . $orderModel['merchant_id'], 3000);   //单位毫秒  pay.商户id

            if ($resource) {

                try {
                    //更新订单状态
                    $params = [
                        'orderno' => $ddh,    //系统订单号
                        'up_orderno' => $response_data['sys_order_no'],   //上游单号
                        'amount' => $response_data['amount']      //金额
                    ];
                    $result = $this->orderFinish($params);

                } catch (\Exception $e) {
                    exit('错误信息'.$e);
                } finally {
                    $redislock->unlock(['resource' => 'pay.' . $orderModel['merchant_id'], 'token' => $resource['token']]);
                }
            }else{
                Log::write('获取用户锁失败:'.$ddh,'error');
                exit('locked error');
            }

            exit('success');
        }
        exit('sign error');
    }

    //签名
    private function _sign(array $data, string $key): string
    {
        ksort($data);

        $sign_str = '';

        foreach ($data as $k => $v) {
            if ($v == '' || $k == 'sign') {
                continue;
            }
            $sign_str .= $k . '=' . $v . '&';
        }
        $sign_str .= 'key=' . $key;
        $sign = strtoupper(md5($sign_str));
        return $sign;
    }
    private function _xsign(array $data, string $key): string
    {
        ksort($data);
        $sign_str = '';

        foreach ($data as $k => $v) {
            if ($v == '' || $k == 'sign') {
                continue;
            }
            $sign_str .= $k . '=' . $v . '&';
        }
        $sign_str = rtrim($sign_str, '&');
        $sign_str .=  $key;
        $sign = strtoupper(md5($sign_str));
        return $sign;
    }
}