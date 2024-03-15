<?php

namespace app\admin\controller\api;

use app\admin\model\ApiType;
use app\common\controller\Backend;
use app\common\model\ApiChannel;

/**
 * 支付类型
 *
 * @icon fa fa-circle-o
 */
class Type extends Backend
{

    protected $noNeedRight = ['getAccount','items'];
    /**
     * ApiType模型对象
     * @var \app\admin\model\ApiType
     */
    protected $model = null;

    protected $modelValidate = true;

    protected $modelSceneValidate = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ApiType;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("defaultList", $this->model->getDefaultList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     *获取该接口类型下的账户通过apichannel表
     */
    public function getAccount(){

        $id = $this->request->param('id/d',0);

        $list = ApiChannel::getAccountByType($id);

        $this->success('','',$list);
    }

    /**
     * 查看
     */
    public function index()
    {

        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model->useGlobalScope(false)
                ->with(['rule'])
                ->where($where)
                ->order($sort, $order)
                ->count();


            $list = $this->model->useGlobalScope(false)
                ->with(['rule'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {


            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }



    public function items(){
        $list = ApiType::getOpenList();
        return json($list);
    }

}
