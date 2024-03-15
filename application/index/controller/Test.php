<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Order;
use fast\Http;
use fast\Random;
use fast\Rsa;
use think\Config;
use think\Cookie;
use think\Hook;

/**
 * Test.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-05
 */
class Test extends Frontend
{

    protected $noNeedLogin = ['*'];


    public function _initialize()
    {
        parent::_initialize();
    }

    public function notify()
    {

        //默认商户号
        $merchant_id = \config('site.testmerchant');
        $userModel = \app\common\model\User::getByMerchantId($merchant_id);
        if (is_null($userModel)) {
            $this->error('支付体验商户不存在！');
        }
        if ($this->request->isPost()) {

            $data = $this->request->only(
                'merId,orderId,sysOrderId,desc,attach,orderAmt,status,nonceStr,sign'
            );

            $rules = [
                'merId|商户号' => 'require|max:24',
                'orderId|订单号' => 'require|max:64',
                'sysOrderId|系统订单号' => 'require|max:64',
                'desc|描述' => 'require|chsDash|max:64',
                'attch|附加信息' => 'alphaDash|max:64',
                'orderAmt|订单金额' => 'require|float',
                'status|状态' => 'require|in:0,1',
                'nonceStr|随机字符串' => 'require|max:32',
                'sign|签名' => 'require'
            ];
            $result = $this->validate($data, $rules);
            if(true !== $result){
                exit($result);
            }
            $publicKey = config('site.public_key');
            $md5Key = $userModel->md5key;
            if (verifyApiSign($data,$md5Key,$publicKey)) {

                exit('success');
            }else{
                exit('sign error');
            }
        }
        $this->error('禁止访问！');
    }

    /**
     * 支付体验
     */
    public function index()
    {

        //默认商户号
        $merchant_id = \config('site.testmerchant');
        $userModel = \app\common\model\User::getByMerchantId($merchant_id);
        if (is_null($userModel)) {
            $this->error('支付体验商户不存在！');
        }

        if ($this->request->isPost()) {

            //表单验证
            $data = $this->request->only(['channel', 'amount']);
            $rules = [
                'amount|充值金额' => 'require|number',
                'channel|支付方式' => 'require|alphaDash|max:24'
            ];
            $result = $this->validate($data, $rules);

            $result = $this->validate($data, $rules);
            if (true !== $result) {
                $this->error($result);
            }


            //获取商户md5和私钥
            $private_key = \config('site.testmerchant_private_key');
            $md5_key = $userModel->md5key;

            if (empty($private_key) || empty($userModel->public_key)) {
                $this->error('体验商户未配置！');
            }


            //组装参数
            $api = \config('site.gateway');

            $api = $api . '/Pay';
            //请求格式
            $data = [
                'merId' => $merchant_id,    //商户号
                'orderId' => 'TEST' . time(),     //订单号，值允许英文数字
                'orderAmt' => sprintf('%.2f', $data['amount']),     //订单金额,单位元保留两位小数
                'channel' => $data['channel'],         //支付通道编码
                'desc' => '爱心捐赠',          //简单描述，只允许英文数字 最大64
                'attch' => '',          //附加信息,原样返回
                'smstyle' => '1',         //用于扫码模式（sm），仅带sm接口可用，默认0返回扫码图片，为1则返回扫码跳转地址。
                'userId' => '',           //用于识别用户绑卡信息，仅快捷接口可用。
                'ip' => $this->request->ip(), //用户的ip地址必传，风控需要
                'notifyUrl' => url('/index/test/notify'),   //异步返回地址
                'returnUrl' => url('/index/test/backurl'),     //同步返回地址
                'nonceStr' => Random::alnum('32'),   //随机字符串不超过32位
            ];


            //生成签名
            $data['sign'] = makeApiSign($data, $md5_key, $private_key);

            $resp = Http::post($api, $data);

            $result = \json_decode($resp, true);

            if (!is_array($result)) {
                $this->error($resp);
            } else {
                if ($result['code'] == 0) {
                    $this->error($result['msg']);
                }
                $this->success($result['msg'], '', [
                    'payurl' => $result['data']['payurl']
                ]);
            }
        }
        $this->assign('list', $userModel->getApiList());

        $this->assign('title', '支付体验');
        return $this->fetch();
    }

    public function backurl()
    {

        $data = $this->request->only(
            'merId,orderId,sysOrderId,desc,attach,orderAmt,status,nonceStr,sign'
        );

        $rules = [
            'merId|商户号' => 'require|max:24',
            'orderId|订单号' => 'require|max:64',
            'sysOrderId|系统订单号' => 'require|max:64',
            'desc|描述' => 'require|chsDash|max:64',
            'attch|附加信息' => 'alphaDash|max:64',
            'orderAmt|订单金额' => 'require|float',
            'status|状态' => 'require|in:0,1',
            'nonceStr|随机字符串' => 'require|max:32',
            'sign|签名' => 'require'
        ];

        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        $orderModel = Order::get(['orderno' => $data['merId'] . $data['orderId']]);

        if (is_null($orderModel)) {
            $this->error('订单不存在');
        }
        $userModel = \app\common\model\User::getByMerchantId($data['merId']);
        if (is_null($userModel)) {
            $this->error('商户不存在');
        }

        $publicKey = config('site.public_key');
        $md5Key = $userModel->md5key;

        if (verifyApiSign($data,$md5Key,$publicKey)) {

            //充值订单就跳转到商户后台
            if($orderModel['style'] == '1'){
                $this->redirect(\config('site.frontend_url'));
            }

            $status = $orderModel['status'];
            if($status!='1'){
                $ddhft = session('ddhft'); //订单刷新次数
                if (!empty($ddhft) && $ddhft > 2) {
                    session('ddhft', NULL);
                    $status = 0;
                } else {
                    $ddhft = empty($ddhft) ? 1 : $ddhft + 1;
                    session('ddhft', $ddhft);
                    $status = 2;
                }
            }else{
                session('ddhft', NULL);
            }
            $this->assign('orderno', $data['orderId']);
            $this->assign('status', $status);
            $this->assign('money', $data['orderAmt']);
            return $this->view->fetch();
        } else {
            $this->error('验签失败!');
        }

    }

    
    /**
     * 模拟接口支付页面
     */
    public function api()
    {

        $orderno = $this->request->param('orderno', '');
        //转到测试接口的backurl
        $url = url('/api/pay/backurl', [
            'code' => 'leiting',
            'orderno' => $orderno
        ]);
        
        return $url;


    }


}