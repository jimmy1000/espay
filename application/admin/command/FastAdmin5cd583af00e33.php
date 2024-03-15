<?php

namespace app\admin\command;

use app\common\controller\Backend;

/**
 * 提现账单
 *
 * @icon fa fa-circle-o
 */
class FastAdmin5cd583af00e33 extends Backend
{
    
    /**
     * Pay模型对象
     * @var \app\admin\model\Pay
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Pay;
        $this->view->assign("styleList", $this->model->getStyleList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("daifustatusList", $this->model->getDaifustatusList());
        $this->view->assign("notifyStatusList", $this->model->getNotifyStatusList());
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
        $this->error('该功能不存在');
    }

    /**
     * @internal
     */
    public function edit()
    {
        $this->error('该功能不存在');
    }

    /**
     * @internal
     */

    public function del($ids = "")
    {
        $this->error('不可删除');
    }




}
