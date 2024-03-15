<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use think\Db;

/**
 * 通知记录
 *
 * @icon fa fa-circle-o
 */
class NotifyLog extends Backend
{

    protected $relationSearch = true;

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


    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('myorder')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('myorder')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
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
    public function edit($ids = null)
    {
    }


    public function clearall()
    {
        Db::execute("TRUNCATE `ep_notify_log`");
        $this->success('清除成功');
    }

}
