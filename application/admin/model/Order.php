<?php

namespace app\admin\model;

use app\common\model\OrderAgent;
use app\common\model\User;
use think\Db;
use think\Model;
use think\Queue;
use think\Session;


class Order extends Model
{



    // 表名
    protected $name = 'order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'style_text',
        'status_text',
        'notify_status_text',
        'paytime_text',
        'repair_text',
        'repair_time_text'
    ];


    protected function getReqInfoAttr($value)
    {

        $req = [];
        parse_str($value, $req);

        return $req;
    }


    public function getStyleList()
    {
        return ['0' => __('Style 0'), '1' => __('Style 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
//        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2')];
    }

    public function getNotifyStatusList()
    {
        return ['0' => __('Notify_status 0'), '1' => __('Notify_status 1'), '2' => __('Notify_status 2')];
    }

    public function getRepairList()
    {
        return ['0' => __('Repair 0'), '1' => __('Repair 1')];
    }


    public function getStyleTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['style']) ? $data['style'] : '');
        $list = $this->getStyleList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getNotifyStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['notify_status']) ? $data['notify_status'] : '');
        $list = $this->getNotifyStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPaytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['paytime']) ? $data['paytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRepairTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['repair']) ? $data['repair'] : '');
        $list = $this->getRepairList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getRepairTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['repair_time']) ? $data['repair_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPaytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setRepairTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function apitype()
    {
        return $this->belongsTo('ApiType', 'api_type_id', 'id', '', 'LEFT')->setEagerlyType(0);
    }

    public function upstream()
    {
        return $this->belongsTo('ApiUpstream', 'api_upstream_id', 'id', '', 'LEFT')->setEagerlyType(0);

    }

    public function account()
    {
        return $this->belongsTo('ApiAccount', 'api_account_id', 'id', '', 'LEFT')->setEagerlyType(0);

    }

    public function user()
    {
        return $this->belongsTo('User', 'merchant_id', 'merchant_id', 'LEFT')->setEagerlyType(0);
    }


    /**
     * 手动退单
     * @param $order_id
     */
    public static function chargeback($order_id)
    {

        $orderModel = self::get($order_id);

        $userModel = $orderModel->user;

        if (is_null($orderModel) || is_null($userModel)) {
            return false;
        }

        //检查商户余额
        Db::startTrans();

        try {

            if ($userModel['money'] < $orderModel['have_money']) {
                exception('商户余额不足，无法撤单。');
            }

            //判断代理
            $agentList = OrderAgent::all([
                'order_id' => $orderModel->id
            ]);

            //先检查一下是否有余额不足的
            if (count($agentList) > 0) {
                $agentList = collection($agentList)->toArray();
                foreach ($agentList as $k => $agent) {
                    if ($agent['money'] <= 0) {
                        continue;
                    }
                    $agentModel = User::get([
                        'merchant_id' => $agent['merchant_id']
                    ]);
                    $agentList[$k]['user_id'] = $agentModel['id'];
                    if (!is_null($agentModel) && $agentModel['money'] < $agent['money']) {
                        exception('代理商户【' . $agentModel['merchant_id'] . '】余额不足，无法撤单。');
                    }
                }
            }
            //减掉用于余额
            User::money(-$orderModel['have_money'], $userModel['id'], '订单撤销资金撤回：撤销金额' . $orderModel['have_money'] . '元', $orderModel['orderno']);
            //减掉代理的钱
            if (count($agentList) > 0) {
                foreach ($agentList as $agent) {
                    if ($agent['money'] <= 0) {
                        continue;
                    }
                    User::money(-$agent['money'], $agent['user_id'], '订单撤销资金撤回：撤销金额' . $agent['money'] . '元', $orderModel['orderno']);
                    OrderAgent::destroy($agent['id']);
                }
            }
            $orderModel->status = '0';
            $orderModel->save();

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            exception($e->getMessage());
        }
    }

    /**
     * 更新订单状态
     * @param $params
     */
    public static function orderFinish($params)
    {

        $orderno = $params['orderno'];
        $up_orderno = $params['up_orderno'];
        $amount = $params['amount'];

        $orderModel = \app\common\model\Order::get([
            'sys_orderno' => $orderno
        ]);

        if (is_null($orderModel)) {
            return [0, '订单不存在'];
        }

        //已经支付
        if ($orderModel->status != 0) {
            return [0, '该订单支付成功'];
        }

        $money1 = number_format($orderModel['total_money'], 2, "", ".");
        $money2 = number_format($amount, 2, "", ".");

        if ($money1 != $money2) {
            return [0, '订单金额不一致'];
        }

        // 获取用户
        $userModel = $orderModel->user;

        if (is_null($userModel)) {
            return [0, '用户不存在'];
        }
        /**
         *
         * 计算代理费用
         */

        $agentMoneyArray = 0; //代理金额明细数组
        $agentMoneyAll = 0;  //代理金额汇总数额
        if ($userModel['agent_id'] != '0') {
            $agentData = array(
                'merchant_id' => $userModel['agent_id'],                  //代理ID
                'rate' => $orderModel['rate'],                      //订单费率
                'jkfl' => $orderModel['channel_rate'],              //通道默认费率
                'jkid' => $orderModel['api_type_id'],                        //接口类型
                'money' => $orderModel['total_money'],                 //订单金额
                'level' => 1,
            );
            $agentMoneyArray = Order::agentMoney($agentData);
            if (is_array($agentMoneyArray)) {
                //计算代理总费用
                foreach ($agentMoneyArray as $iDlMoneyArr) {
                    $agentMoneyAll += $iDlMoneyArr['money'];
                }
            }
        }


        //开启事务
        Db::startTrans();

        try {
            $admin = Session::get('admin'); //获取管理员
            $orderModel->status = '1';
            $orderModel->paytime = time();
            $orderModel->up_orderno = $params['up_orderno'];
            $orderModel->agent_money = $agentMoneyAll;

            $orderModel->repair = '1';  //补单
            $orderModel->repair_admin_id = $admin['id'];        //管理员id
            $orderModel->repair_time = time();                  //补单时间

            $orderModel->save();

            //给用户加上余额
            $money = $orderModel['have_money'];
            if($orderModel->style == '1'){
                User::money($money,$userModel->id,'充值：' . $orderModel['total_money'] . '元，扣除手续费到账：' . $money . '元',$orderModel->orderno);
            }else{
                User::money($money,$userModel->id,'资金流水记录：订单金额' . $orderModel['total_money'] . '元，到账金额' . $money . '元',$orderModel->orderno);
            }
            //代理金额增加
            if (!empty($agentMoneyArray)) {

                foreach ($agentMoneyArray as $agentMoney) {

                    if ($agentMoney['money'] <= 0) {
                        continue;
                    }
                    OrderAgent::create([
                        'order_id' => $orderModel->id,
                        'level' => $agentMoney['level'],
                        'merchant_id' => $agentMoney['merchant_id'],
                        'money' => $agentMoney['money'],
                        'rate' => $agentMoney['userfl']
                    ]);
                    User::money($agentMoney['money'], $agentMoney['user_id'], '代理资金流水记录：订单金额' . $orderModel['total_money'] . '元，到账金额' . $agentMoney['money'] . '元', $orderModel->orderno,'3');
                }
            }

            //判断是否限额
            $channelModel = $orderModel->channel();

            if ($channelModel['daymoney'] > 0) {
                //改变订单每日额度
                $channelModel->todaymoney = $channelModel->todaymoney + $orderModel['total_money'];
                $channelModel->save();
            }

            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            return [0, $e->getMessage()];
        }


        if($orderModel->style!='1'){
            //加入通知队列 发送异步通知
            Queue::push('app\common\job\Notify',['order_id'=>$orderModel->id]);
        }
        return [1, 'success'];


    }

    /**
     * 获取代理金额
     * @param $data
     */
    public static function agentMoney($data)
    {


        $userModel = User::getByMerchantId($data['merchant_id']);

        if (is_null($userModel) || $userModel['group_id'] != '2' || $userModel['status'] == 'hidden' || $userModel['ifagentmoney'] != '1') {
            return false;
        }

        //开始计算费率差

        //获取代理当前类型的费率

        $agentRate = User::getAgentRate($userModel['id'], $data['jkid']);


        //如果代理没有设置费率则按照通道的默认费率来进行分红
        if (!$agentRate) {
            $agentRate = $data['jkfl'];
        }

        //如果都没有设置的话就改为当前的费率
        if($agentRate <= 0){
            $agentRate = $data['rate'];
        }

        $agentMoney = 0;        //代理金额
        $nowRate = $agentRate; //当前使用的费率

        // 如果订单费率 > 代理费率
        if ($agentRate < $data['rate']) {
            // 代理金额 订单费率 - 代理的费率 * 金额
            $agentMoney = $data['money'] * ($data['rate'] - $agentRate) / 100;  //代理金额
            $agentMoney = number_format($agentMoney, 2);
            if ($agentMoney <= 0) {
                $agentMoney = 0;
            }
        } else {
            $nowRate = $data['rate'];
        }

        if (empty($data['level'])) {
            $data['level'] = 1;
        }

        $moneyBuffer[] = array(
            'level' => $data['level'],
            'money' => $agentMoney,                      //加了多少钱
            'merchant_id' => $data['merchant_id'],
            'user_id' => $userModel['id'],
            'fl' => $data['rate'],                       //当前费率
            'userfl' => $nowRate,                       //当前计算的费率
            'jkfl' => $data['jkfl'],                    //当前接口费率
            'leavemoney' => $userModel['money']       //加之前的金额
        );

        //继续计算下级
        if (3 > $data['level'] && $userModel['agent_id'] > 0) {
            //计算代理费用
            $agentData = array(
                'merchant_id' => $userModel['agent_id'],
                'rate' => $nowRate,                  //当前代理的费率
                'jkfl' => $data['jkfl'],             //接口的费率
                'jkid' => $data['jkid'],
                'money' => $data['money'],
                'level' => $data['level'] + 1,
            );
            $tmp = self::agentMoney($agentData);

            if ($tmp) {
                foreach ($tmp as $i => $iTmp) {
                    $moneyBuffer[] = $iTmp;
                }
            }
        }
        return $moneyBuffer;

    }


}
