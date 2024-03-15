<?php

namespace app\admin\command;

use app\common\controller\Backend;

/**
 * 通知记录
 *
 * @icon fa fa-circle-o
 */
class FastAdmin5ccd4608c5292 extends Backend
{

    /**
     * NotifyLog模型对象
     * @var \app\admin\model\NotifyLog
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\NotifyLog;

    }


    public function index()
    {

    }


    /**
     * @internal  mixed
     */
    public function add()
    {
        
    }

    /**
     * @internal  mixed
     */
    public function edit()
    {

    }


}
