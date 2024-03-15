<?php

namespace app\admin\controller\api;

use app\admin\model\ApiUpstream;
use app\common\controller\Backend;

/**
 * 接口上游
 *
 * @icon fa fa-circle-o
 */
class Upstream extends Backend
{
    
    /**
     * ApiUpstream模型对象
     * @var \app\admin\model\ApiUpstream
     */
    protected $model = null;

    protected $modelSceneValidate = true;

    protected $modelValidate = true;


    protected $noNeedRight = ['items'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ApiUpstream;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    public function get()
    {
        $id = $this->request->param('id/d','');


        $row = ApiUpstream::get($id);

        if(is_null($row)){
            return $this->error('上游记录不存在!');
        }

        $this->success('获取成功!','',['params'=>$row['params']]);

    }

    public function items()
    {
        $list = collection(ApiUpstream::all())->toArray();

        return json($list);
    }


}
