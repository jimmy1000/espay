<?php
/**
 * Feiyun.php
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

class Feiyun extends Base
{


    public function pay($params)
    {

        $config = $params['config'];

        $request_data = array();
        $request_data['attach'] = $params['sys_orderno'];//非必填项
        $request_data['member_sn'] = $params['sys_orderno'];//订单编号
        $request_data['order_amount'] = $params['total_money'];
        $request_data['payment_code'] = 'unionpay';
        $request_data['member_id'] = $config['id'];
        $request_data['sign'] = $this->_sign($request_data, $config['key']);//执行签名
        $request_data['callback_url'] = $params['notify_url'];//异步回调地址,不参与签名
        $pay_url = 'http://118.190.129.166/fyf/mobile/index.php?act=pay&op=pay';//下单接口

        $resp = Http::post($pay_url, $request_data);

        $result = json_decode($resp, true);

        if ($result['code'] == 200) {
            $pay_url = $result['data']['pay_url'];//支付URL，直接跳转或者转为二维码
            return [1,$pay_url];
        }


        return [0,$result['message']];

    }


    public function notify()
    {

        Log::write('飞云回调信息:' . http_build_query($_REQUEST), 'CHANNEL');

        $response_data = array();
        $response_data['member_id'] = $_REQUEST['member_id'];
        $response_data['platform_order_sn'] = $_REQUEST['platform_order_sn'];
        $response_data['member_sn'] = $_REQUEST['member_sn'];
        $response_data['payment_time'] = $_REQUEST['payment_time'];
        $response_data['order_amount'] = $_REQUEST['order_amount'];
        $response_data['payment_code'] = $_REQUEST['payment_code'];
        $response_sign = $_REQUEST['sign'];


        $ddh = $response_data['member_sn'];

        $config = $this->getOrderConfig($ddh);

        $key = $config['key']; //秘钥，请获取最新秘钥


        $get_sign = $this->_sign($response_data,$key);//执行签名
        if($response_sign==$get_sign) {
            $orderModel = Order::get(['sys_orderno' => $ddh]);
            //同一时刻 同一用户只能处理一个
            $redislock = redisLocker();
            $resource = $redislock->lock('pay.' . $orderModel['merchant_id'], 3000);   //单位毫秒

            if ($resource) {
                try {
                    //更新订单状态
                    $params = [
                        'orderno' => $ddh,    //系统订单号
                        'up_orderno' => $response_data['platform_order_sn'],   //上游单号
                        'amount' => $response_data['order_amount'] / 1       //金额
                    ];
                    $result = $this->orderFinish($params);
                } catch (\Exception $e) {

                } finally {
                    $redislock->unlock(['resource' => 'pay.' . $orderModel['merchant_id'], 'token' => $resource['token']]);
                }
            }else{
                Log::write('获取用户锁失败:'.$ddh,'error');
                exit('locked error');
            }

            exit('ok');
        }
        exit('sign error');
    }

    //签名
    private function _sign($data, $key)
    {
        $str = '';
        ksort($data);//字母排序，升序
        foreach ($data as $value) {
            if ($value) {
                $str .= $value;//拼接参数值
            }
        }
        $str .= $key;//拼接key
        $signValue = md5($str);//32位md5,小写
        return $signValue;
    }
}