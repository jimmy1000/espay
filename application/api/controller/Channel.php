<?php
/**
 * Channel.php
 * 易聚合支付系统
 * =========================================================

 * ----------------------------------------------
 *
 *
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-04-27
 */

namespace app\api\controller;

use app\common\controller\Api;



class Channel extends Api {


    protected $noNeedLogin = [];

    //不需要权限检查的方法
    protected $noNeedRight = ['index','md5','changeapi'];


    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){

        $user = $this->auth->getUser();

        $list = $user->getApiList();

        $this->success('',$list);
    }

    /**
     * 获取用户的md5key
     */
    public function md5(){

        $rules = [
            'paypassword|支付密码' => 'require|length:6,16'
        ];

        $data = [
            'paypassword' => $this->request->param('paypassword', '')
        ];

        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        //验证支付密码是否正确
        $flag = \app\common\model\User::verifyPayPassword($data['paypassword'],$this->auth->id);

        if(!$flag){
            $this->error('支付密码输入不正确!');
        }

        //获取用户的md5key
        $this->success('获取成功!',['md5'=>$this->auth->getUser()->md5key]);

    }

    /**
     * 开发设置修改
     */
    public function changeapi(){


        $data = $this->request->only(['codeStyle','smsCode','googleCode','public_key','req_url']);

        $rules = [
            'public_key|商户公钥' => 'require',
            'req_url|请求地址' => 'require',
            'codeStyle|验证码类型' => 'require|in:1,2',
            'smsCode|短信验证码' => 'requireIf:codeStyle,1',
            'googleCode|谷歌验证码' => 'requireIf:codeStyle,2'
        ];

        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        $code = $data['codeStyle'] == 1 ? $data['smsCode'] : $data['googleCode'];
        $this->checkUserCode($data['codeStyle'], $code, 'changeapi');
        $pem = chunk_split($data['public_key'], 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $public_key = openssl_pkey_get_public($pem);
        if(!$public_key){
            $this->error('公钥格式有误，请检查后重新输入。');
        }

        $user = $this->auth->getUser();
        $user->save([
            'public_key'=>$data['public_key'],
            'req_url'=>$data['req_url']
        ]);

        $this->success('设置成功');

    }
}