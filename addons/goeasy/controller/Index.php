<?php

namespace addons\goeasy\controller;

use app\admin\library\Auth;
use think\addons\Controller;

/**
 * goeasy推送
 */
class Index extends Controller
{
    public function _initialize()
    {
        parent::_initialize();
        $auth = Auth::instance();
        if ($auth->isLogin() === false) {
            $this->error('请登录后台再测试');
        }
    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function send()
    {
        $goeasy = new \addons\goeasy\library\Goeasy();

        $content = $this->request->post('content');
        $type = $this->request->post('type');
        if ($type == 'user') {
            $userId = $this->request->post('user_id');
            $userId = $userId == '' ? 'common' : $userId;
            $result = $goeasy->sendToUser($userId, $content);
        } else {
            $adminId = $this->request->post('admin_id');
            $adminId = $adminId == '' ? 'common' : $adminId;
            $result = $goeasy->sendToAdmin($adminId, $content);
        }
        if (!$result) {
            $this->error('发送失败！ ' . $goeasy->error);
        }
        $this->success('已发送');
    }
}
