<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use app\common\model\NotifyLog;
use fast\Date;
use fast\Http;
use fast\Random;
use fast\Rsa;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\Session;

/**
 * 订单管理
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\Order
     */
    protected $model = null;

    protected $multiFields = [];


    protected $relationSearch = true;


    protected $modelValidate = true;

    protected $modelSceneValidate = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order;
        $this->view->assign("styleList", $this->model->getStyleList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("notifyStatusList", $this->model->getNotifyStatusList());
        $this->view->assign("repairList", $this->model->getRepairList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


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
                ->with(['apitype', 'upstream', 'account'])
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with(['apitype', 'upstream', 'account'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
            }
            $result = array("total" => $total, "rows" => $list);


            //当天的统计信息
            $todayStatistics = $this->model
                ->whereTime('createtime', 'today')
                ->where('status', 'in', '1,2')
                ->field('COALESCE(sum(total_money),0) as `allMoney`,COALESCE(sum(have_money),0) as `haveMoney`,COALESCE(sum(agent_money),0) as `agentMoney`,COALESCE(sum(upstream_money),0) as `upstreamMoney`')
                ->find();

            $todayStatistics = $todayStatistics->toArray();


            $allStatistics = $this->model
                ->where('status', 'in', '1,2')
                ->field('COALESCE(sum(total_money),0) as `allMoney`,COALESCE(sum(have_money),0) as `haveMoney`,COALESCE(sum(agent_money),0) as `agentMoney`,COALESCE(sum(upstream_money),0) as `upstreamMoney`')
                ->find();

            $allStatistics = $allStatistics->toArray();


            //列表金额
            $listMoney = $this->model
                ->with(['apitype'])
                ->where($where)->sum('total_money');
            $listHaveMoney = $this->model
                ->with(['apitype'])
                ->where($where)->sum('have_money');

            $result['extend'] = [
                'todayMoney' => $todayStatistics['allMoney'],
                'todayHaveMoney' => $todayStatistics['haveMoney'],
                'todayAgentMoney' => $todayStatistics['agentMoney'],
                'todayUpstreamMoney' => $todayStatistics['upstreamMoney'],
                'todayExpenseMoney' => bcadd(bcadd($todayStatistics['upstreamMoney'], $todayStatistics['agentMoney']), $todayStatistics['haveMoney']),
                'allMoney' => $allStatistics['allMoney'],
                'allExpenseMoney' => bcadd(bcadd($allStatistics['haveMoney'], $allStatistics['agentMoney']), $allStatistics['upstreamMoney']),
                'listMoney'=>$listMoney,
                'listHaveMoney'=>$listHaveMoney
            ];


            return json($result);
        }
        return $this->view->fetch();
    }


    public function add()
    {
        $this->error('该功能不存在');
    }

    public function edit($ids = null)
    {
        $this->error('该功能不存在');
    }

    public function multi($ids = "")
    {
        $this->error('该功能不存在');
    }


    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }

            //只有未支付的可以删除
            $list = $this->model->where($pk, 'in', $ids)->where('status', '0')->select();
            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error('只可以删除未支付的订单');
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    /**
     * 订单详情
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
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
        $orderModel = $this->model->find($data['id']);

        $this->assign('order', $orderModel);

        return $this->fetch();

    }

    /**
     * 手动通知
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notify()
    {
        $data = $this->request->only('id');
        $rules = [
            'id|编号' => 'require|number'
        ];
        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }
        $orderModel = $this->model->find($data['id']);

        if ($orderModel->style == '1') {
            $this->error('充值订单不能重发通知');
        }

        $orderno = substr($orderModel->orderno, strlen($orderModel->merchant_id));


        //发送通知
        $post_data = [
            'merId' => $orderModel->merchant_id,          //商户号
            'orderId' => $orderno,            //商户订单号
            'sysOrderId' => $orderModel->sys_orderno,     //系统订单号
            'desc' => $orderModel->req_info['desc'],      //描述
            'orderAmt' => $orderModel->total_money,       //订单金额
            'status' => $orderModel->status,              //通知状态 1为支付成功
            'nonceStr' => Random::alnum('32')        //随机字符串
        ];

        if (!empty($orderModel->req_info['attch'])) {
            $post_data['attch'] = $orderModel->req_info['attch'];       //附加信息
        }

        $userModel = $orderModel->user;

        $post_data['sign'] = makeApiSign($post_data, $userModel->md5key, config('site.private_key'));

        if ($this->request->isPost()) {

            $notifyUrl = $orderModel->req_info['notifyUrl'];
            $result = Http::post($notifyUrl, $post_data);
            //开启事务
            Db::startTrans();
            try {
                NotifyLog::log($orderModel->id, $notifyUrl, $post_data, $result);
                if ($result == 'success') {
                    $orderModel->notify_status = '2';
                    $orderModel->notify_count = $orderModel->notify_count + 1;
                } else {
                    $orderModel->notify_status = '1';
                    $orderModel->notify_count = $orderModel->notify_count + 1;
                }
                $orderModel->save();
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            if ($result == 'success') {
                $this->success('手动通知成功:' . $result);
            }
            $this->error('手动通知失败:' . $result);
        }

        $this->assign('order', $orderModel);
        $this->assign('post_data', urldecode(http_build_query($post_data)));

        return $this->fetch();

    }

    /**
     * 手动补单
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function repair()
    {

        $data = $this->request->only('id');
        $rules = [
            'id|编号' => 'require|number'
        ];

        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        $orderModel = $this->model->find($data['id']);

        if ($orderModel['status'] != '0') {
            $this->error('补单失败,该订单已成功！');
        }

        if ($this->request->isPost()) {

            //先检查一下安全码对不对

            $safeCode = $this->request->param('safeCode');
            if(md5(md5($safeCode))!= config('easypay.safeCode')){

                $this->error('安全码输入错误！！');
            }


            //同一时刻 同一用户只能处理一个
            $redislock = redisLocker();
            $resource = $redislock->lock('pay.' . $orderModel['merchant_id'], 3000);   //单位毫秒

            if ($resource) {
                try {
                    //更新订单状态
                    $params = [
                        'orderno' => $orderModel['sys_orderno'],    //系统订单号
                        'up_orderno' => 'EP' . $orderModel['sys_orderno'],   //上游单号
                        'amount' => $orderModel['total_money']       //金额
                    ];
                    $result = \app\admin\model\Order::orderFinish($params);
                } catch (\Exception $e) {
                    $redislock->unlock(['resource' => 'pay.' . $orderModel['merchant_id'], 'token' => $resource['token']]);
                    $this->error($e->getMessage());
                } finally {
                    $redislock->unlock(['resource' => 'pay.' . $orderModel['merchant_id'], 'token' => $resource['token']]);
                }
            } else {
                $this->error('系统处理订单繁忙，请重试');
            }

            if ($result[0] == 1) {
                $this->success('补单成功！');
            }
            $this->error($result[1]);

        }
        $this->assign('order', $orderModel->toArray());

        return $this->fetch();
    }

    public function chargeback()
    {


        $data = $this->request->only('id');
        $rules = [
            'id|编号' => 'require|number'
        ];

        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }

        $orderModel = $this->model->find($data['id']);

        if ($orderModel['status'] == '0') {
            $this->error('退单失败,该订单未支付！');
        }

        //获取用户锁
        $redislock = redisLocker();
        $resource = $redislock->lock('pay.' . $orderModel['merchant_id'], 3000);   //单位毫秒
        if ($resource) {
            try {
                \app\admin\model\Order::chargeback($orderModel->id);

                $redislock->unlock(['resource' => 'pay.' . $orderModel['merchant_id'], 'token' => $resource['token']]);

            } catch (\Exception $e) {

                $redislock->unlock(['resource' => 'pay.' . $orderModel['merchant_id'], 'token' => $resource['token']]);

                $this->error($e->getMessage());
            }
        } else {
            $this->error('系统处理订单繁忙，请重试');
        }

        $this->success('退单成功');

    }

    /**
     * 清除未支付订单
     */
    public function clearfail()
    {

        $yestarday = Date::unixtime('day', '-1');
        $this->model->where([
            'status' => '0',
            'createtime' => ['<=', $yestarday]
        ])->delete();

        $this->success('清除成功。');
    }


    /**
     * 签名
     * @param $params
     * @param $md5
     * @param $pub_key
     */
    private function sign($params, $md5, $pri_key)
    {

        ksort($params);
        reset($params);
        $arg = '';
        foreach ($params as $key => $val) {
            //空值不参与签名
            if ($val == '' || $key == 'sign') {
                continue;
            }
            $arg .= ($key . '=' . $val . '&');
        }
        $arg = $arg . 'key=' . $md5;

        $rsa = new Rsa('', $pri_key);

        return $rsa->sign($arg);
    }


    /**
     * 首页订单统计
     */
    public function chart(){

        $interval = 5 ; //间隔单位分钟
        $list = 5;     //十条记录
        $mins = [];
        $now = time();
        $minute = \date('i',$now);

        $mod = $minute % $interval;
        if($mod!=0){
            $now = $now - $mod * 60;
        }
        $allList = [];
        $succList = [];
        $succRateList = [];
        for($i = 1;$i <= $list; $i++){

            $end = $now - ($interval * ($list - $i) * 60);

            $endStr = \date('H:i',$end);

            $start = $end - ($interval * 60);

            $mins[] = $endStr;

            //所有的订单数量
            $allList[$endStr] = \app\common\model\Order::where([
                'createtime' => ['between', [$start, $end]]
            ])->count();

            //成功订单数
            $succList[$endStr] = \app\common\model\Order::where([
                'createtime' => ['between', [$start, $end]],
                'status'=>['in','1,2']
            ])->count();


            //实时成功率
            if($allList === 0 ||  $succList[$endStr] === 0){
                $succRateList[$endStr] = 0;
            }else{
                $succRateList[$endStr] = number_format($succList[$endStr] / $allList[$endStr] * 100,2);
            }

            //实时订单金额
            $moneyList[$endStr] = \app\common\model\Order::where([
                'createtime' => ['between', [$start, $end]],
                'status'=>['in','1,2']
            ])->sum('total_money');

        }

        $result = [
            'allList'=>array_values($allList),
            'succList'=>array_values($succList),
            'succRateList'=>array_values($succRateList),
            'moneyList'=>array_values($moneyList),
            'mins'=>$mins
        ];

        $this->success('获取成功。',null,$result);

    }


}
