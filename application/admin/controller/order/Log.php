<?php

namespace app\admin\controller\order;

use app\admin\model\ApiLog;
use app\common\controller\Backend;
use fast\Date;

/**
 * 接口日志管理
 *
 * @icon fa fa-circle-o
 */
class Log extends Backend
{

    /**
     * ApiLog模型对象
     * @var \app\admin\model\ApiLog
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ApiLog;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * @internal
     */
    public function add()
    {
        $this->error('不存在的方法');
    }

    /**
     * @internal
     */
    public function edit($ids = null)
    {
        $this->error('不存在的方法');
    }

    public function detail()
    {
        $data = $this->request->only('id');
        $rules = [
            'id|编号' => 'require|number'
        ];

        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        $model = $this->model->find($data['id']);

        $this->assign('row', $model);

        return $this->fetch();
    }


    public function delall()
    {
        $time = Date::unixtime('day', -3);
        ApiLog::destroy(function ($query) use ($time) {
            $query->whereTime('createtime', '<=', $time);
        });
        $this->success('日志清空成功');
    }

}
