<?php
/**
 * Channel.php
 * 易聚合支付系统
 * =========================================================

 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-04-27
 */

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\ApiChannel;
use app\common\model\OrderAgent;
use app\common\model\UserApichannel;
use think\Db;
use think\Exception;
use think\Validate;


class Agent extends Api {


    protected $noNeedLogin = [];

    //不需要权限检查的方法
    protected $noNeedRight = [];


    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 代理开户
     */
    public function adduser(){

        $username = $this->request->param('username', '');
        $password = $this->request->param('password', '');
        $email = $this->request->param('email', '');
        $mobile = $this->request->param('mobile', '');
        $bio = $this->request->param('bio','');


        $extend = [];
        $extend['group_id'] = 1;
        $extend['bio'] = $bio;
        $extend['agent_id'] = $this->auth->merchant_id;

        //是否允许代理注册
        if (config('site.agent_switch') == 1) {
            $extend['group_id'] = $this->request->param('agent') == 0 ? 1 : 2;
        }

        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }

        if($bio && !Validate::is($bio,'chsDash')){
            $this->error('备注只能是汉字、字母、数字、下划线以及破折号');
        }

        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        //代理开户是否锁定用户
        if(config('site.agent_adduser_switch') == 1){
            $extend['status'] = 'hidden';
        }

        $ret = $this->auth->register($username, $password, $email, $mobile, $extend);

        if ($ret) {
            $this->success('开户成功');
        } else {
            $this->error($this->auth->getError());
        }

    }

    /**
     * 获取用户列表
     */
    public function userlist(){

        $user_id = $this->auth->merchant_id;

        $agentId = $this->request->param('agentId/d');


        if($user_id == $agentId){
            $user_list = \app\common\model\User::getChildList($user_id);
        }

        $agentModel = \app\common\model\User::get(['merchant_id'=>$agentId,'group_id'=>2]);

        if(is_null($agentModel)){
            $this->error('数据不存在');
        }

        //检查代理的上级是否为当前用户
        $parentModel = $agentModel->parent();
        if(!is_null($parentModel) && ($parentModel['merchant_id'] == $user_id || $parentModel['agent_id'])){
            $user_list = \app\common\model\User::getChildList($agentId);
        }else{
            $this->error('数据不存在,或者您无权查看');
        }

        $this->success('获取成功!',$user_list);

    }


    /**
     * 编辑修改备注
     */
    public function changeBio()
    {

        $data = $this->request->only(['merchantId','bio']);
        $user_id = $this->auth->merchant_id;

        $rules = [
           'merchantId|下级商户号'=>'require|number|max:24',
           'bio|备注'=>'require|chsDash|max:100'
        ];
        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        //检查一下是否有权限更改当前用户的备注

        $userModel = \app\common\model\User::get(['merchant_id'=>$data['merchantId']]);

        if($userModel['agent_id'] != $user_id){
            $this->error('您无权修改此商户的备注！');
        }

        $userModel->bio = $data['bio'];
        $userModel->save();
        $this->success('修改备注成功!');

    }
    /**
     * 获取下级的费率
     */

    public function userrate(){

        $data = $this->request->only('merchant_id');
        $rules = [
            'merchant_id|商户号'=>'require|number|max:24'
        ];
        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        //检查是否下级会员
        $merchant = \app\common\model\User::getByMerchantId($data['merchant_id']);

        if($merchant['agent_id']!=$this->auth->merchant_id){
            $this->error('您无权查看或者设置该商户的费率！');
        }


        //获取该会员的通道
        $myapi_list = $this->auth->getUser()->getApiList2();
        $api_list = $merchant->getApiList2();

        $result = [];

        foreach ($api_list as $code=>$api){

            $my_status = true;
            $my_rate = false;
            if(empty($myapi_list[$code])){
                $my_status = false;
            }else{
                $my_rate = $myapi_list[$code]['user_rate'];
            }

            $result[] = [
                'name'=>$api['name'],   //接口名称
                'code'=>$code,          //接口代码
                'id'=>$api['id'],       //接口id
                'rate'=>$api['user_rate'],  //当前费率,默认都是0
                'mystatus'=>$my_status,     //是否有资格设置费率
                'myrate'=>$my_rate          //我的费率
            ];
        }

        $this->success('获取成功',$result);

    }

    public function setuserrate(){


        //系统是否开启
        if(config('site.agent_change_rate') != '1'){
            $this->error('系统暂未开启此功能!');
        }

        $data = $this->request->only('merchant_id,rates');

        $rules = [
            'merchant_id|商户号'=>'require|number|max:24',
            'rates|费率'=>'require|array'
        ];
        $result = $this->validate($data, $rules);

        if (true !== $result) {
            $this->error($result);
        }

        //检查是否下级会员
        $merchant = \app\common\model\User::getByMerchantId($data['merchant_id']);

        if($merchant['agent_id']!=$this->auth->merchant_id){
            $this->error('您无权查看或者设置该商户的费率！');
        }

        //锁住这个商户防止重复插入
        $redislock = redisLocker();
        $resource = $redislock->lock('rate.' . $data['merchant_id'], 3000);   //单位毫秒

        if(!$resource){
            sleep(1);
            $resource = $redislock->lock('rate.' . $data['merchant_id'], 3000);   //单位毫秒
        }

        if(!$resource){
            $this->error('当前系统火爆，请重新提交');
        }



        $myapi_list = $this->auth->getUser()->getApiList2();
        $api_list = $merchant->getApiList2();

        $rate_list = $data['rates'];

        $insert_data = [];
        $update_data = [];
        foreach ($rate_list as $item){
            $code = $item['code'];
            $rate = $item['rate'];
            $type_id = $item['id'];

            if(empty($api_list[$code])){

                continue;
            }
            //检查是否可以设置
            if(empty($myapi_list[$code])){
                continue;
            }

            if($rate < 0 ){
                continue;
            }
            //检测费率设置是否合法,必须大于自己的费率
            if( $rate > 0 && $rate < $myapi_list[$code]['user_rate']){
                continue;
            }

            $model = UserApichannel::get(function ($query) use($type_id,$merchant){
                $query->where([
                    'api_type_id'=>$type_id,
                    'user_id'=>$merchant['id']
                ]);
            });

            if(is_null($model)){
                array_push($insert_data,[
                    'api_type_id'=>$type_id,
                    'user_id'=>$merchant['id'],
                    'rate'=>$rate,
                    'status'=>'1'
                ]);
            }else{
                array_push($update_data,[
                    'id'=>$model->id,
                    'rate'=>$rate
                ]);
            }
        }


        Db::startTrans();
        try{
            //处理数据
            $model = new UserApichannel();
            $model->insertAll($insert_data);
            $model->isUpdate()->saveAll($update_data);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $this->error('设置失败!');
        }finally {
            $redislock->unlock(['resource' => 'rate.' . $data['merchant_id'], 'token' => $resource['token']]);
        }

        $this->success('设置成功!');

    }

    public function orderlist(){

        $data = $this->request->only(['merchant_id','orderno', 'status', 'api_type_id', 'date']);

        $rules = [
            'merchant_id|商户号'=>'number',
            'orderno|订单号' => 'alphaDash',
            'status|订单状态' => 'in:0,1',
            'api_type_id|订单类型' => 'integer',
            'date|日期范围' => 'array'
        ];

        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        $where = [];

        //获取该代理的所有下级
        $agentList = \app\common\model\User::getListByAgent($this->auth->merchant_id);

        $agentIds = array_column($agentList,'merchant_id');


        //如果没有填写商户号
        if(!empty($data['merchant_id'])){
            //如果不在这里面
            if(!in_array($data['merchant_id'],$agentIds)){
                $this->error('您无权查看此商户的数据');
            }
            $where = [
                'order.merchant_id' => $data['merchant_id']
            ];
            $where2 = [
                'merchant_id' => $data['merchant_id']
            ];
            $where3 = [
                'merchant_id' => $data['merchant_id']
            ];
        }else{

            $where = [
                'order.merchant_id' => ['in',$agentIds]
            ];
            $where2 = [
                'merchant_id'=>['in',$agentIds]
            ];

            $where3 =[
                'merchant_id'=>['in',$agentIds]
            ];
        }


        if (isset($data['orderno']) && $data['orderno'] != '') {
            $where['order.orderno'] = ['like','%'.$data['orderno'].'%'];
            $where2['orderno'] = ['like','%'.$data['orderno'].'%'];
        }

        
        //订单状态
        if (isset($data['status']) && $data['status'] != '') {
            $where['order.status'] = $data['status'];
            $where2['status'] =  $data['status'];
        }
        //请求类型
        if (!empty($data['api_type_id'])) {
            $where['order.api_type_id'] = $data['api_type_id'];
            $where2['api_type_id'] = $data['api_type_id'];
        }

        //时间
        if (isset($data['date']) && is_array($data['date'])) {
            $data['date'][0] = $data['date'][0] / 1000;
            $data['date'][1] = $data['date'][1] / 1000;
            $where['order.createtime'] = ['between time', $data['date']];
            $where2['createtime'] = ['between time', $data['date']];

        }



        //排序字段
        $orderField = $this->request->param('orderField', 'id');
        $sort = 'DESC';
        //分页字段
        $page = $this->request->param('page/d', 1);
        $pageLimit = 10;    //每页显示10条数据
        $offset = ($page - 1) * $pageLimit;


        $total = \app\common\model\Order::with(['apitype' => function ($query) {
            $query->withField('name');
        }])->where($where)->count();
        $list = \app\common\model\Order::
        with(['apitype' => function ($query) {
            $query->withField('name');
        }])
            ->where($where)
            ->order($orderField, $sort)
            ->limit($offset, $pageLimit)
            ->select();

        foreach ($list as $k => $v) {
            $v->visible(['merchant_id','orderno', 'sys_orderno', 'total_money', 'have_money', 'style_text', 'status', 'status_text', 'notify_status_text', 'createtime_text', 'paytime_text', 'apitype']);
        }
        $list = collection($list)->toArray();


        //统计信息
        $extend = [];


        //今日订单金额
        $extend['today'] = \app\common\model\Order::where($where3)->whereTime('createtime','today')->sum('total_money');
        //今日成功订单金额
        $extend['todaysuccess'] = \app\common\model\Order::where($where3)->where('status','1')->whereTime('createtime','today')->sum('total_money');

        //昨日订单金额
        $extend['yesterday'] = \app\common\model\Order::where($where3)->whereTime('createtime','yesterday')->sum('total_money');
        $extend['yesterdaysuccess'] = \app\common\model\Order::where($where3)->where('status','1')->whereTime('createtime','yesterday')->sum('total_money');

        //当前列表金额
        $extend['all'] = \app\common\model\Order::where($where2)->sum('total_money');
        //当前列表订单数量
        $extend['allcount'] = \app\common\model\Order::where($where2)->count();

        // 未支付订单数量成功率为0
        if(isset($where2['status']) && $where2['status'] == '0'){
            $extend ['successRate'] = 0;
        }else{
            //当前列表成功数量
            $where2['status'] = '1';
            $extend['succCount'] = $succCount = \app\common\model\Order::where($where2)->count();
            if($extend['allcount']  == 0){
                $extend['successRate'] = 100;
            }else{
                $extend['successRate'] =  number_format($succCount / $extend['allcount']  * 100,2);
            }
        }

        $this->success('获取数据成功！', [
            'total' => $total,
            'list' => $list,
            'limit' => $pageLimit,
            'extend'=>$extend
        ]);


    }


    /**
     * 代理统计详情
     */
    public function detail()
    {

        $userMolde = $this->auth->getUser();
        $data = [];
        //下级商户数
        $data['childs'] = \app\common\model\User::where(['agent_id'=>$userMolde['merchant_id']])->count();
        $childrens = \app\common\model\User::getListByAgent($userMolde['merchant_id']);
        $data['childrens'] = count($childrens);

        //当天的代理金
        $data['all'] = OrderAgent::where(['merchant_id'=>$this->auth->merchant_id])->sum('money');
        $data['today'] = OrderAgent::whereTime('createtime','today')->where(['merchant_id'=>$this->auth->merchant_id])->sum('money');
        //当月代理金
        $data['month'] = OrderAgent::whereTime('createtime','month')->where(['merchant_id'=>$this->auth->merchant_id])->sum('money');

        //商户交易量

         $this->success('获取数据成功。',$data);
    }


}