<?php
/**
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-08
 */

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Bank;
use app\common\model\Order;
use fast\Http;
use fast\Random;
use fast\Rsa;
use think\Cache;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Log;

class Ewm extends Frontend
{

    protected $noNeedLogin = ['*'];


    public function _initialize()
    {
        parent::_initialize();
    }


    public function show()
    {

        $data = $this->request->only(['orderno','qr','key']);


        $order_no = $data['orderno'];

//        $qr = htmlspecialchars_decode($data['qr']);
//        //验证key
//        $sign=md5($order_no.$qr.config('token.key'));
//
//        if($data['key']!= $sign){
//            $this->error('二维码信息有误，请重新获取支付链接。');
//        }



        $qr = Cache::get('qr.'.$order_no);

        if(empty($qr)){
            $this->error('订单二维码已失效!');
        }




        //获取订单状态
        $orderModel = Order::get([
            'orderno'=>$order_no
        ]);

        if (is_null($orderModel)){
            $this->error('该订单不存在。');
        }

        if($orderModel->status == '1'){
            $this->error('该订单已支付。');
        }


        $apitype = $orderModel->apitype;
        $upstream = $orderModel->upstream;
        $returnUrl = \config('site.gateway').'/Pay/backurl/code/'.$upstream['code'].'?orderno='.$orderModel['sys_orderno'];
        $data = [
            'api'=>$apitype,
            'order'=>$orderModel,
            'qr'=>$qr,
            'key'=>md5($order_no.$orderModel['merchant_id'].config('token.key')),
            'returnUrl'=>$returnUrl
        ];

        $this->assign('data',$data);

        return $this->fetch();
    }

    public function getstatus(){

        $data = $this->request->only(['orderno','key']);

        $rules = [
            'orderno|订单号'=>'require|alphaDash|max:64' ,
            'key'=>'require|alphaNum|max:32'
        ];

        $result = $this->validate($data, $rules);

        if ($result !== true) {
            $this->error($result);
        }

        //获取订单状态
        $orderModel = Order::get([
            'orderno'=>$data['orderno']
        ]);

        if(is_null($orderModel)){
            $this->error('订单不存在。');
        }
        //验证key
        $sign = md5($data['orderno'].$orderModel['merchant_id'].config('token.key'));
        if($data['key'] != $sign){
            $this->error('数据异常。');
        }
        $this->success('','',[
            'status'=>$orderModel['status']
        ]);

    }

}