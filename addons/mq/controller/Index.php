<?php
/**
 * index.php
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
namespace addons\mq\controller;

use addons\mq\library\Mq;
use addons\mq\model\MqAccount;
use addons\mq\model\MqOrder;
use app\common\model\Order;

class Index extends Base {


    public function index()
    {

        Mq::orderFinish('357087213130874880');
        return $this->view->fetch('/index');
    }

    /**
     * 显示二维码页面
     * @throws \think\exception\DbException
     */
    public function show()
    {
        $orderno = $this->request->param('orderno');
        $config = get_addon_config('mq');

        $orderModel = MqOrder::get([
            'orderno'=>$orderno
        ]);

        if(is_null($orderModel)){
            $this->error('订单数据不存在！');
        }
        //检测订单是否超时
        if(time() - $orderModel->createtime > (intval($config['orderValidity']) * 60) ){
            $this->error('该笔订单已超时！');
        }

        //检测是否支付成功

        $sysorderModel = Order::get([
            'sys_orderno'=>$orderno
        ]);


        if($sysorderModel->status == '1'){
            $this->error('该订单已支付。');
        }

        //根据不同的type来显示不同的页面内容
        $mqAccountModel = MqAccount::get($orderModel->mq_account_id);
        if(is_null($mqAccountModel)){
            $this->error('收款账号不存在！');
        }


        $this->assign('orderno',str_replace($sysorderModel->merchant_id,'',$sysorderModel->orderno));           //订单号
        $this->assign('price',$orderModel->price);                  //订单金额
        $this->assign('realprice',$orderModel->realprice);          //实际付款金额
        $this->assign('createtime',$orderModel->createtime);        //订单创建时间

        $order = $orderModel->toArray();
        $key = md5($orderno.$orderModel->realprice.config('token.key'));

        $order['queryurl'] = addon_url('mq/index/query',[
            'orderno'=>$orderno,
            'price'=>$orderModel->realprice,
            'key'=>$key
        ],'',true);

        $order['remainseconds'] = $config['orderValidity'] * 60;

        $this->assign('order',$order);
        $this->assign('type',$orderModel->type);


        //支付宝
        if($orderModel->type == 'alipay'){

            $domain = Mq::getDomain();
            $alipays = 'alipays://platformapi/startapp?appId=20000691&url='; // 2019年04月07日 原appid 20000067 替换成 20000691

            $url = $domain . '/alipay.html?u='. $mqAccountModel['qr'] .'&a='.$orderModel->realprice;

            //二维码地址
            $qrurl = urlencode($url);
            $qrurl = config('site.url').'/qrcode/build?text='.$qrurl.'&logo=zfbsm';

            $this->assign('qrcode',$qrurl);                               //二维码地址
            $this->assign('schema',$alipays.urlencode($url));
            return $this->fetch('/show');
        }

        //微信
        if($orderModel->type == 'wechat'){

            $this->assign('qrcode',cdnurl($mqAccountModel['qr']));
            return $this->fetch('/wechat');
        }

        $this->error('支付接口不存在！');

    }

    /**
     * 查询订单状态
     */
    public function query(){


        $data = $this->request->only(['orderno','price','key']);

        $mykey = md5($data['orderno'].$data['price'].config('token.key'));

        $result = [];

        $config = get_addon_config('mq');


        if($mykey != $data['key']){

            $result = [
                'code'=>0,
                'msg'=>'签名不正确'
            ];
            return json_encode($result);

        }else{
            $orderModel = Order::get(['sys_orderno'=>$data['orderno']]);
            $mqOrderModel = MqOrder::get(['orderno'=>$data['orderno']]);
            if(is_null($orderModel) || is_null($mqOrderModel)){
                $result = [
                    'code'=>0,
                    'msg'=>'订单不存在'
                ];
                return json_encode($result);
            }

            //检测订单是否支付成功
            if($orderModel->status == '1'){
                $result = [
                    'code'=>1,
                    'data'=>[
                        'status'=>'success',
                        'returnurl'=>\config('site.gateway').'/Pay/backurl/code/mianqian'.'?orderno='.$data['orderno']
                    ]
                ];
                return json_encode($result);
            }

            //检测订单是否超时
            if(time() - $mqOrderModel->createtime > (intval($config['orderValidity']) * 60) ){
                $result = [
                    'code'=>1,
                    'data'=>[
                        'status'=>'expired'
                    ]
                ];

                return json_encode($result);
            }

            $result = [
                'code'=>1,
                'data'=>[
                    'status'=>'inprogress'
                ]
            ];

            return json_encode($result);


        }


    }
}