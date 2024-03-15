<?php

namespace app\api\controller;

use addons\geetest\library\GeetestLib;
use app\common\controller\Api;
use fast\Random;
use Think\Validate;

/**
 * 极验验证码
 */
class Geetest extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {

        // 读取插件配置
        $config = get_addon_config('geetest');
        if (!$config['appid'] || !$config['appkey']) {
            $this->error('请先在后台中配置极验证的参数信息');
        }
        // 优先取网站的登录用户ID，没有的情况下取Session中的值，再没有的情况下随机生成
        $user_id = $this->auth->id ? $this->auth->id : (session('geetest_user_id') ? session('geetest_user_id') : Random::alnum(8));
        $gtSdk = new GeetestLib($config['appid'], $config['appkey']);
        $data = array(
            "user_id" => $user_id, # 网站用户id
            "client_type" => $this->request->isMobile() ? 'h5' : 'web', #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => $this->request->ip() # 请在此处传输用户请求验证时所携带的IP
        );

        // 判断极验证服务器状态
        $status = $gtSdk->pre_process($data, 1);
        cache('geetest_status', $status);
        $this->success('6666', $gtSdk->get_response());

    }


    public function test(){
        $rule['captcha'] = 'require|captcha';
        $data['captcha'] ='ok';
        $validate = new Validate($rule, [], ['captcha' => __('Captcha')]);
        $result = $validate->check($data);
        if (!$result) {
            $this->error($validate->getError());
        }

        $this->success('6666');


    }
}
