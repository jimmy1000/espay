<?php
/**
 * Bankcard.php
 * 易聚合支付系统
 * =========================================================

 * ----------------------------------------------
 *
 *
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-09
 */
namespace app\api\controller;

use addons\goeasy\library\Goeasy;
use app\common\controller\Api;
use app\common\model\ApiChannel;
use app\common\model\UserApichannel;
use app\common\model\UserLog;
use easypay\Notify;
use think\Db;
use think\Exception;
use think\Validate;

class Bankcard extends Api{

    protected $noNeedLogin = [];

    //不需要权限检查的方法
    protected $noNeedRight = ['*'];


    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 银行卡列表
     */
    public function index(){

        $data = $this->request->only(['name', 'ka', 'status']);

        $rules = [
            'name|姓名'=>'chsAlpha|max:32',
            'ka|卡号'=>'number|max:24',
            'status|状态'=>'in:0,1',
        ];
        $messages = [
            'status.in'=>'状态错误'
        ];

        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        //查询条件
        $where = [
            'merchant_id' => $this->auth->merchant_id
        ];



        if(!empty($data['name'])){
            $where['name'] = ['like','%'.$data['name'].'%'];
        }

        if(!empty($data['ka'])){
            $where['ka'] = ['like','%'.$data['ka'].'%'];
        }

        if (isset($data['status']) && $data['status'] != '') {
            $where['status'] = $data['status'];
        }



        //排序字段
        $orderField = $this->request->param('orderField', 'id');
        $sort = 'DESC';
        //分页字段
        $page = $this->request->param('page/d', 1);
        $pageLimit = 10;    //每页显示10条数据
        $offset = ($page - 1) * $pageLimit;


        //数据总数
        $total = \app\common\model\Bankcard::where($where)->count();
        $list = \app\common\model\Bankcard::where($where)
            ->order($orderField, $sort)
            ->limit($offset, $pageLimit)
            ->select();
        $list = collection($list)->toArray();

        $this->success('获取数据成功！', [
            'total' => $total,
            'list' => $list,
            'limit' => $pageLimit
        ]);
    }
    /**
     * 银行卡添加
     */
    public function add()
    {
        $data = $this->request->only(['name','ka','bank','province','city','zhihang']);
        $rules = [
            'name|姓名'=>'require|chsAlpha|max:32',
            'ka|卡号'=>'require|number|max:24',
            'bank|银行'=>'require|chsAlpha|max:32',
            'province|省份'=>'require|chs|max:24',
            'city|城市'=>'require|chs|max:24',
            'zhihang|支行'=>'require|chsAlphaNum|max:255',
        ];
        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        $data['merchant_id'] = $this->auth->merchant_id;

        //如果开启审核
        if(config('site.ifcheckka') == '1'){
            $data['status'] = '0';
        }else{
            $data['status'] = '1';      //自动通过
        }

        \app\common\model\Bankcard::create($data);

        UserLog::addLog($this->auth->merchant_id, '添加银行卡【' . $data['ka'] . '】');

        Notify::bankcard();

        $this->success('添加银行卡成功');

    }

    /**
     * 获取通过审核的卡
     */
    public function normal(){

        //查询条件
        $where = [
            'merchant_id' => $this->auth->merchant_id,
            'status'=>'1'
        ];
        //排序字段
        $orderField = $this->request->param('orderField', 'id');
        $sort = 'DESC';
        $list = \app\common\model\Bankcard::where($where)
            ->order($orderField, $sort)
            ->select();
        $list = collection($list)->toArray();

        $this->success('获取数据成功！', [
            'list' => $list,
        ]);
    }

    /**
     * 编辑银行卡
     */
    public function edit()
    {

        $data = $this->request->only(['name','ka','bank','province','city','zhihang','id']);
        $rules = [
            'name|姓名'=>'require|chsAlpha|max:32',
            'ka|卡号'=>'require|number|max:24',
            'bank|银行'=>'require|chsAlpha|max:32',
            'province|省份'=>'require|chs|max:24',
            'city|城市'=>'require|chs|max:24',
            'zhihang|支行'=>'require|chsAlphaNum|max:255',
            'id|编号'=>'require|number'
        ];
        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        if(config('site.ifcheckka') == '1'){
            $data['status'] = '0';
        }else{
            $data['status'] = '1';      //自动通过
        }

        $where = [
            'merchant_id'=>$this->auth->merchant_id,
            'id'=>$data['id']
        ];

        unset($data['id']);

        \app\common\model\Bankcard::update($data,$where);

        UserLog::addLog($this->auth->merchant_id, '修改编号为'.$where['id'].'的银行卡信息');

        Notify::bankcard();

        $this->success('修改成功');

    }
    /**
     * 删除银行卡
     */
    public function del()
    {
        $data = $this->request->only('id');
        $rules = [
            'id|编号'=>'require|number'
        ];
        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        $where = [
            'merchant_id'=>$this->auth->merchant_id,
            'id'=>$data['id']
        ];

        \app\common\model\Bankcard::destroy($where);

        UserLog::addLog($this->auth->merchant_id, '删除编号为'.$data['id'].'的银行卡');

        $this->success('删除成功');
    }


}