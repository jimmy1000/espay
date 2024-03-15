<?php

namespace app\admin\controller\finance;

use app\common\controller\Backend;

/**
 * 会员余额变动管理
 *
 * @icon fa fa-circle-o
 */
class Moneylog extends Backend
{
    
    /**
     * Moneylog模型对象
     * @var \app\admin\model\finance\Moneylog
     */
    protected $model = null;

    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\finance\Moneylog;

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
                ->with('user')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('user')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);
            $listMoney = $this->model
                ->with(['user'])
                ->where($where)->sum('moneylog.money');

            $result['extend'] = [
                'listMoney'=>$listMoney
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

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
}
