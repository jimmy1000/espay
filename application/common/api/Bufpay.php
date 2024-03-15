<?php
/**
 * Bufpay.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-07-13
 */

namespace app\common\api;

use app\common\model\Order;
use fast\Http;
use think\Log;

class Bufpay extends Base
{


    public function pay($params)
    {
        $config = $params['config'];
        $api = "https://bufpay.com/api/pay/{$config['merchant_id']}?format=json";
        $request_data = array();
        $request_data['name'] = $params['sys_orderno'];
        $request_data['pay_type'] = $params['channel'] == 'zfbsm' ? 'alipay':'wechat';
        $request_data['price'] = $params['total_money'];
        $request_data['order_id'] =$params['sys_orderno'];
        $request_data['order_uid'] = $params['merId'];
        $request_data['notify_url'] = $params['notify_url'];
        $request_data['return_url'] = $params['return_url'];
        $request_data['feedback_url'] = '';
        $request_data['sign'] = md5($request_data['name']. $request_data['pay_type'].$request_data['price'].$request_data['order_id'].$request_data['order_uid'].$request_data['notify_url'].$request_data['return_url'].$request_data['feedback_url'].$config['key']);
        $resp = Http::post($api, $request_data);
        $result = json_decode($resp, true);
        if(empty($result)){
            return [0,'请求API错误'];
        }
        if ($result['status']!='ok'){
            return [0,'渠道错误:'.$result['status']];
        }
        return [1,$this->qrcode($result['qr'])];

    }


    public function notify()
    {

        Log::write('Buf回调信息:' . http_build_query($_POST), 'CHANNEL');

        $notify_data = $_POST;
        
        $ddh = $notify_data['order_id'];

        $config = $this->getOrderConfig($ddh);

        $secret = $config['key'];

        $sign = md5($notify_data['aoid'].$notify_data['order_id'].$notify_data['order_uid'].$notify_data['price'].$notify_data['pay_price'].$secret);

        if($sign == $notify_data['sign']){

            $orderModel = Order::get(['sys_orderno' => $ddh]);
            //同一时刻 同一用户只能处理一个
            $redislock = redisLocker();
            $resource = $redislock->lock('pay.' . $orderModel['merchant_id'], 3000);   //单位毫秒
            if ($resource) {
                try {
                    //更新订单状态
                    $params = [
                        'orderno' => $ddh,    //系统订单号
                        'up_orderno' => $notify_data['aoid'],   //上游单号
                        'amount' => $notify_data['price'] / 1       //金额
                    ];
                    $result = $this->orderFinish($params);
                } catch (\Exception $e) {

                } finally {
                    $redislock->unlock(['resource' => 'pay.' . $orderModel['merchant_id'], 'token' => $resource['token']]);
                }
            }else{
                Log::write('获取用户锁失败:'.$ddh,'error');
                http_response_code(500);
                exit('locked error');
            }
            exit("success");
        }
        http_response_code(500);
        exit('sign error');
    }

}