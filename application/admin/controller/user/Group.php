<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

/**
 * 会员组管理
 *
 * @icon fa fa-users
 */
class Group extends Backend
{

    /**
     * @var \app\admin\model\UserGroup
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('UserGroup');
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    public function add()
    {
        $nodeList = \app\admin\model\UserRule::getTreeList();
        $this->assign("nodeList", $nodeList);
        return parent::add();
    }

    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $rules = explode(',', $row['rules']);
        $nodeList = \app\admin\model\UserRule::getTreeList($rules);
        $this->assign("nodeList", $nodeList);
        return parent::edit($ids);
    }

    public function del($ids = "")
    {
        $ids = explode(',',$ids);
        if(in_array('1',$ids) || in_array('2',$ids) ){
            $this->error('不可删除的组别');
        }
        return parent::del($ids);
    }

}
