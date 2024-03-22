<?php
namespace app\common\api;

use app\common\model\Order;
use fast\Http;
use think\Log;

class hongyun extends Base
{


    public function pay($params)
    {

        $config = $params['config'];
        $upstream_config= $params['upstream_config'];
        $hongyun_mchNo = $upstream_config[0]['default'];
        $hongyun_key = $upstream_config[1]['default'];
        $request_data = array();
        $request_data['mchNo'] = $hongyun_mchNo;//商户号
        $request_data['mchOrderNo'] = $params['sys_orderno'];//商户订单号
        $request_data['productId'] = $params['channel'];//支付产品ID，具体请与平台管理联系
        $request_data['amount'] = $params['total_money']*100;//支付金额
        $request_data['clientIp'] = $params['ip'];//客户端ip
        $request_data['notifyUrl'] = $params['notify_url'];//回调地址
        $request_data['reqTime'] = time();//请求时间
        $request_data['sign']=$this->_sign($request_data, $hongyun_key);//执行签名
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
            $pay_url = $result['data']['payData'];//支付URL，直接跳转或者转为二维码
            return [1,$pay_url];
        }
        return [0,$result['message']];

    }


    public function notify()
    {

        Log::write('鸿运回调信息:' . http_build_query($_REQUEST), 'CHANNEL');
        $response_data = array();
        $response_data['payOrderId'] = $_REQUEST['payOrderId']; //支付系统订单号
        $response_data['mchNo'] = $_REQUEST['mchNo'];   //商户号
        $response_data['mchOrderNo'] = $_REQUEST['mchOrderNo']; //返回商户传入的订单号
        $response_data['ifCode'] = $_REQUEST['ifCode']; //支付接口编码
        $response_data['amount'] = $_REQUEST['amount'];//支付金额,单位分
        $response_data['state'] = $_REQUEST['state'];//支付订单状态2-支付成功5-测试冲正(已成功且标记为测定订单时返回)
        $response_data['createdAt'] = $_REQUEST['createdAt'];
        $response_data['reqTime'] = $_REQUEST['reqTime'];
        $response_sign = $_REQUEST['sign'];


        $ddh = $response_data['mchOrderNo']; //获取商户传入的订单号
        $config = $this->getOrderConfig($ddh);//获取订单上游配置
        $key = $config['1']['default']; //秘钥，请获取最新秘钥

        $get_sign = $this->_sign($response_data,$key);//执行签名

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
                        'up_orderno' => $response_data['payOrderId'],   //上游单号
                        'amount' => $response_data['amount'] / 100       //金额
                    ];
                    $result = $this->orderFinish($params);
                    var_dump($result);exit();
                } catch (\Exception $e) {
                    exit('错误信息'.$e);
                } finally {
                    $redislock->unlock(['resource' => 'pay.' . $orderModel['merchant_id'], 'token' => $resource['token']]);
                }
            }else{
                Log::write('获取用户锁失败:'.$ddh,'error');
                exit('locked error');
            }

            exit('0');
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
}