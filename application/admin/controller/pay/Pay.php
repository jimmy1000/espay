<?php

namespace app\admin\controller\pay;

use app\common\controller\Backend;
use app\common\model\ApiAccount;
use app\common\model\PayOrder;
use app\common\model\User;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\Queue;

/**
 * 提现账单
 *
 * @icon fa fa-circle-o
 */
class Pay extends Backend
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
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
            }
            $result = array("total" => $total, "rows" => $list);


            //统计信息

            //当天所有订单金额
            $todayMoney = \app\admin\model\Pay::whereTime('createtime', 'today')->sum('money');
            //当天成功的订单金额
            $todaySuccMoney = \app\admin\model\Pay::whereTime('createtime', 'today')->where('status', '1')->sum('money');

            //全部金额
            $allMoney = \app\admin\model\Pay::sum('money');
            $allSuccMoney = \app\admin\model\Pay::where('status', '1')->sum('money');


            //列表金额
            $listMoney = $this->model->where($where)->sum('money');

            $listChargeMoney = $this->model
                ->where($where)->sum('charge');

            $result['extend'] = [
                'todayMoney' => $todayMoney,
                'todaySuccMoney' => $todaySuccMoney,
                'allMoney' => $allMoney,
                'allSuccMoney' => $allSuccMoney,
                'listMoney'=>$listMoney,
                'listChargeMoney'=>$listChargeMoney

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
        $this->error('该功能不存在');
    }

    /**
     * @internal
     */
    public function edit($ids = "")
    {
        $this->error('该功能不存在');
    }

    /**
     * @internal
     */

    public function del($ids = "")
    {
        $this->error('提款单不可删除');
    }


    /**
     * 批量更新
     */
    public function multi($ids = "")
    {
        $ids = $ids ? $ids : $this->request->param("ids");
        if ($ids) {
            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);

                if ($values['status'] != '2' && $values['status'] != '1') {
                    $this->error('批量操作只支持冻结和处理操作。');
                }

                $values = array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values || $this->auth->isSuperAdmin()) {
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;
                    Db::startTrans();
                    try {
                        //附加条件
                        $list = $this->model->where($this->model->getPk(), 'in', $ids)->where([
                            'status' => ['in', '0,2'],                  //申请中或者冻结的单子的订单才可以冻结和成功
                            'daifustatus' => ['in', '0,2']     //代付未提交 或者失败才可以冻结和成功
                        ])->select();
                        foreach ($list as $index => $item) {
                            $count += $item->allowField(true)->isUpdate(true)->save($values);
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
                        //批量发送通知
                        if ($values['status'] == '1') {
                            $ids = explode(',', $ids);
                            foreach ($ids as $id) {
                                Queue::push('app\common\job\RepayNotify', ['pay_id' => $id]);
                            }
                        }
                        $this->success();
                    } else {
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 取消账单
     */
    public function cancel()
    {
        $id = $this->request->param('id/d', 0);

        $payModel = $this->model->find($id);

        if (empty($payModel['id'])) {
            $this->error('获取代付订单失败!', null);
        }

        if ($payModel['status'] == '3') {
            $this->error('当前订单已取消!', null);
        }

        if ($payModel['daifustatus'] == '1' || $payModel['daifustatus'] == '3') {
            $this->error('代付提交中和代付成功后不能取消支付!', null);
        }

        $userModel = User::get([
            'merchant_id' => $payModel['merchant_id']
        ]);

        //获取修改用户金额的锁
        $redislock = redisLocker();
        $resource = $redislock->lock('pay.' . $userModel['merchant_id'], 3000);   //单位毫秒


        if ($resource) {
            //更改数据
            Db::startTrans();
            try {
                $payModel->save([
                    'status' => '3'
                ]);
                //给用户返还金额了
                $changemoney = bcadd($payModel['money'], $payModel['charge'], 2);
                User::money($changemoney, $userModel['id'], '提现取消返还金额：' . $changemoney . '元', $payModel['orderno'], '2');
                $userModel->setDec('withdrawal', $payModel['money']);
                Db::commit();
                $redislock->unlock(['resource' => 'pay.' . $userModel['merchant_id'], 'token' => $resource['token']]);
            } catch (\Exception $e) {
                Db::rollback();
                $redislock->unlock(['resource' => 'pay.' . $userModel['merchant_id'], 'token' => $resource['token']]);
                $this->error($e->getMessage(), null);
            }
        } else {
            $this->error('系统处理订单繁忙，请重试');
        }
        $this->success('取消代付订单成功!', null);

    }


    /**
     * 处理
     */
    public function handle()
    {

        //如果是ajax请求
        if ($this->request->isAjax()) {

            $data = $this->request->only(['id', 'daifuid']);

            if (empty($data['daifuid'])) {
                $this->error('请选择代付机构');
            }

            try {
                $result = \app\common\model\Pay::dfSubmit($data['id'], $data['daifuid']);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            if ($result[0] == 0) {
                $this->error($result[1]);
            }
            $this->success('处理成功.', null, [
                'msg' => $result[1]
            ]);
        }

        $id = $this->request->param('id/d', 0);
        $payModel = $this->model->find($id);

        $userModel = \app\admin\model\User::get([
            'merchant_id' => $payModel['merchant_id']
        ]);

        //用户默认的代付账户
        $daifuid = '';
        if ($userModel['daifuid'] > 0) {
            $daifuid = $userModel['daifuid'];
        }

        //代付提交记录
        $payOrderList = PayOrder::where([
            'pay_id' => $id
        ])->with(['account' => function ($query) {
            $query->withField('name');
        }])->select();

        $payOrderList = collection($payOrderList)->toArray();

        $this->assign('list', $payOrderList);
        $this->assign('daifuid', $daifuid);
        $this->assign('pay', $payModel->toArray());

        return $this->fetch();
    }

    /**
     * 查询订单
     */
    public function select()
    {
        $id = $this->request->param('id/d', 0);

        $payOrderModel = PayOrder::get($id);

        if (is_null($payOrderModel)) {
            $this->error('代付订单不存在');
        }

        $payModel = \app\admin\model\Pay::get($payOrderModel['pay_id']);

        if (is_null($payModel)) {
            $this->error('代付订单不存在');
        }
        $accountModel = ApiAccount::get([
            'id' => $payOrderModel['api_account_id'],
            'ifrepay' => '1'
        ]);
        if (is_null($accountModel)) {
            $this->error('代付账户未开启或不存在！');
        }
        $code = $accountModel->upstream->code;
        //调用代付接口的参数
        $params = array(
            'orderno' => $payOrderModel['orderno'],
            'params' => $accountModel['params'],
            'payData' => $payModel->toArray()
        );
        $result = loadApi($code)->repayselect($params);
        if ($result[0] == 0) {
            $this->error($result[1]);
        }
        $this->success('查询成功', null, [
            'msg' => $result[1]
        ]);
    }


}
