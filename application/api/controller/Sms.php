<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;

/**
 * 手机短信接口
 */
class Sms extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * 发送验证码
     *
     * @param string $mobile 手机号
     * @param string $event 事件名称
     */
    public function send()
    {
        $mobile = $this->request->param("mobile",'');
        $event = $this->request->param("event",'');
        $event = $event ? $event : 'register';

        if (!$mobile || !\think\Validate::regex($mobile, "^1[3-9]\d{9}$")) {
            $this->error(__('手机号不正确'.$mobile));
        }
        $last = Smslib::get($mobile, $event);
        if ($last && time() - $last['createtime'] < 60) {
            $this->error(__('发送频繁'));
        }
        $ipSendTotal = \app\common\model\Sms::where(['ip' => $this->request->ip()])->whereTime('createtime', '-1 hours')->count();
        if ($ipSendTotal >= 20) {
            $this->error(__('发送频繁'));
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }

            // 手机号绑定操作
            if($event == 'bindphone'){
                //检测是否登录
                if(!$this->auth->isLogin()){
                    $this->error('操作被拒绝，您未登录！');
                }
                //检测手机号是否已经存在
                $user = User::where('id','NEQ',$this->auth->id)->where('mobile',$mobile)->find();
                if($user){
                    $this->error('该手机号已被注册，如有疑问请联系客服！');
                }
            }

            //手机号解绑操作
            if($event == 'clearmobilebind'){
                //检测是否登录
                if(!$this->auth->isLogin()){
                    $this->error('操作被拒绝，您未登录！');
                }
                $user_info = $this->auth->getUserinfo();
                //如果没有绑定
                if($user_info['mobilebind'] == 0){
                    $this->error('操作被拒绝，该手机号未被绑定');
                }
                //检查是不是本人的手机号
                if($user_info['mobile'] != $mobile){
                    $this->error('操作被拒绝，手机号不匹配');
                }
            }


            if($event == 'changepaypassword' || $event == 'changepassword' || $event == 'changeapi' || $event == 'repay' || $event == 'batchrepay'){
                //检测是否登录
                if(!$this->auth->isLogin()){
                    $this->error('操作被拒绝，您未登录！');
                }
                $user_info = $this->auth->getUserinfo();
                //如果没有绑定
                if($user_info['mobilebind'] == 0){
                    $this->error('操作被拒绝，您未绑定手机号。');
                }
                $mobile = $user_info['mobile'];
            }

        }
        $ret = Smslib::send($mobile, null, $event);
        if ($ret) {
            $this->success(__('发送成功'));
        } else {
            $this->error(__('发送失败'));
        }
    }

    /**
     * 检测验证码
     *
     * @param string $mobile 手机号
     * @param string $event 事件名称
     * @param string $captcha 验证码
     */
    public function check()
    {
        $mobile = $this->request->request("mobile");
        $event = $this->request->request("event");
        $event = $event ? $event : 'register';
        $captcha = $this->request->request("captcha");

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Smslib::check($mobile, $captcha, $event);
        if ($ret) {
            $this->success(__('成功'));
        } else {
            $this->error(__('验证码不正确'));
        }
    }
}
