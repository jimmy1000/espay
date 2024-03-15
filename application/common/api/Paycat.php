<?php
/**
 * Paycat.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-14
 */

namespace app\common\api;

use fast\Http;
use think\Log;

class Paycat extends Base
{


    /**
     * $params = [
     * 'config' => '',                 //配置参数
     * 'merId' => '',                  //商户号
     * 'sys_orderno' => '',            //订单号
     * 'total_money' => '',            //订单金额
     * 'channel' => '',                //通道代码
     * 'desc' => '',                   //简单描述
     * 'bankcode' => '',               //银行代码
     * 'user_id' => '',                //快捷模式必须
     * 'ip' => $ip,                    //ip地址
     * 'domain' => $domain,            //地址信息
     * 'notify_url' =>'',              //通知地址
     * 'return_url' => '',             //同步跳转地址
     * ];
     * 支付接口
     * @param $params
     */
    public function pay($params)
    {

        $config = $params['config'];

        $mch_id = $config['mch_id'];

        $key = $config['key'];

        $channel = $params['channel'];




        if($channel == 'wxgzh'){
            $api = 'https://www.paycats.cn/v1/pay/wx/cashier';
            $postData = [
                'mch_id' => $mch_id,
                'total_fee' => $params['total_money'] * 100,
                'out_trade_no' => $params['sys_orderno'],
                'body' => empty($params['desc']) ? '订单支付:' . $params['sys_orderno'] : $params['desc'],
                'callback_url'=>$params['return_url']
            ];

            $postData['sign'] = $this->sign($postData, $key);

            return [1,$api.'?'.http_build_query($postData)];

        }



        if($channel == 'zfbsm'){
            $api = 'https://api.paycats.cn/v1/pay/alipay/f2f';
        }else{
            $api = 'https://api.paycats.cn/v1/pay/wx/native';
        }



        $postData = [
            'mch_id' => $mch_id,
            'total_fee' => $params['total_money'] * 100,
            'out_trade_no' => $params['sys_orderno'],
            'body' => empty($params['desc']) ? '订单支付:' . $params['sys_orderno'] : $params['desc'],
            'user_id' => $params['merId']
        ];

        $postData['sign'] = $this->sign($postData, $key);

        $resp = Http::post($api, $postData);

        $result = \json_decode($resp, true);

        if (!is_array($result)) {
            return [0, $resp];
        }


        if ($result['return_code'] == '0') {

            return [1, $this->qrcode($result['code_url'], $channel)];

        }

        return [0, $result['return_message']];
    }

    public function backurl($orderno='')
    {
        if(!empty($_GET['out_trade_no'])){
            $orderno = request()->param('out_trade_no');
        }else{
            $orderno = request()->param('orderno');
        }

        return parent::backurl($orderno);
    }

    /**
     * 通知
     */
    public function notify()
    {
        $param = file_get_contents("php://input");
//
//        Log::write('支付猫回调信息:'.$param,'CHANNEL');

//        $param = 'notify_type=order.succeeded&mch_id=1535171311&order_no=15578070091535171311327418&total_fee=100&out_trade_no=347351326956453888&transaction_id=4200000302201905143997964433&pay_at=2019-05-14+12%3A10%3A21&openid=oUoQBwXSkoQY59XJEvwizKnmy_D8&attach=&sign=EB0FB39629FB79641CA836E9D4578641';

        parse_str($param, $notify_data);

        if (empty($notify_data) || $notify_data['notify_type'] != 'order.succeeded') {

            http_response_code(500);
            exit('error');
        }

        $orderno = $notify_data['out_trade_no'];

        $config = $this->getOrderConfig($orderno);

        $sign = $notify_data['sign'];
        unset($notify_data['sign']);

        $mysign = $this->sign($notify_data, $config['key']);

        if ($mysign == $sign) {

            //加锁处理订单状态
            $redislock = redisLocker();
            $resource = $redislock->lock('pay.' . $orderno, 3000);   //单位毫秒
            if ($resource) {
                try {
                    //更新订单状态
                    $params = [
                        'orderno' => $orderno,    //系统订单号
                        'up_orderno' => $notify_data['order_no'],   //上游单号
                        'amount' => $notify_data['total_fee'] / 100       //金额
                    ];
                    $result = $this->orderFinish($params);

                } catch (\Exception $e) {

                } finally {
                    $redislock->unlock(['resource' => 'pay', 'token' => $resource['token']]);
                }
            }


            http_response_code(200);

            exit('success');


        } else {

            http_response_code(500);
            exit('sign error');
        }


    }

    private function sign(array $data, string $key): string
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