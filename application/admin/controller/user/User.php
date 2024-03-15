<?php

namespace app\admin\controller\user;

use app\admin\model\ApiType;
use app\admin\model\UserApichannel;
use app\common\controller\Backend;
use app\common\model\ApiChannel;
use app\common\model\UserLog;
use fast\Random;
use think\Db;
use think\Exception;
use think\Config;


/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
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
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->hidden(['password', 'salt', 'googlesecret', 'paypassword', 'paysalt', 'token', 'public_key', 'md5key']);
            }
            $result = array("total" => $total, "rows" => $list);

            $allMoney = $this->model->sum('money');
            $allWithDrayMoney = $this->model->sum('withdrawal');
            $result['extend'] = [
                'allMoney' => $allMoney,
                'allWithDrayMoney' => $allWithDrayMoney
            ];

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

    /**
     * 开户
     */
    public function add()
    {

        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), 1, ['class' => 'form-control selectpicker']));

        return parent::add();
    }


    /**
     * 重设md5密钥
     */
    public function resetMd5Key()
    {

        $data = [
            'id' => $this->request->param('id', '')
        ];
        //验证规则
        $result = $this->validate($data, 'User.reset_google_bind');
        if (true !== $result) {
            $this->error($result);
        }
        try {
            \app\admin\model\User::resetMd5Key($data['id']);
            $this->success(__('Reset Key Success'));
        } catch (Exception $e) {
            $this->error(__('Reset Key Error %s', $e->getMessage()));
        }

    }

    /**
     * 清楚用户的谷歌令牌绑定
     */

    public function resetGoogleBind()
    {
        $data = [
            'id' => $this->request->param('id', '')
        ];
        $result = $this->validate($data, 'User.reset_google_bind');
        if (true !== $result) {
            $this->error($result);
        }
        try {
            \app\common\model\User::clearGoogleSecret($data['id']);
            $this->success(__('Reset Google Binding Success'));
        } catch (Exception $e) {
            $this->error(__('Reset Google Binding Error %s', $e->getMessage()));
        }
    }

    /**
     * 清除手机绑定
     */
    public function clearMobileBind()
    {
        $data = [
            'id' => $this->request->param('id', '')
        ];
        $result = $this->validate($data, 'User.clear_mobile_bind');
        if (true !== $result) {
            $this->error($result);
        }
        try {
            \app\common\model\User::clearMobileBind($data['id']);
            $this->success('手机号解绑成功，请通知商户尽快绑定！');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }


    public function apichannel()
    {


        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            $result = $this->validate($row, 'User.apichannel');
            if (true !== $result) {
                $this->error($result);
            }
            Db::startTrans();
            try {
                //插入数组,更新数组
                $inser_data = [];
                $update_data = [];
                foreach ($row['types'] as $typeid) {
                    //数据库中是否存在记录
                    $model = UserApichannel::get(function ($query) use ($typeid, $row) {
                        $query->where([
                            'api_type_id' => $typeid,
                            'user_id' => $row['id']
                        ]);
                    });

                    if (is_null($model)) {
                        array_push($inser_data, [
                            'user_id' => $row['id'],
                            'api_type_id' => $typeid,
                            'api_rule_id' => $row[$typeid]['rule'],   //规则
                            'rate' => $row[$typeid]['rate'],  //自定义的费率
                            'status' => $row[$typeid]['status']
                        ]);
                    } else {
                        array_push($update_data, [
                            'id' => $model->id,
                            'user_id' => $row['id'],
                            'api_type_id' => $typeid,
                            'api_rule_id' => $row[$typeid]['rule'],   //规则
                            'rate' => $row[$typeid]['rate'],  //自定义的费率
                            'status' => $row[$typeid]['status']
                        ]);
                    }
                }
                //处理数据
                $model = new UserApichannel();
                $model->insertAll($inser_data);
                $model->isUpdate()->saveAll($update_data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            return $this->success('设置成功!');
        }


        $data = [
            'id' => $this->request->param('id/d', '')
        ];
        $result = $this->validate($data, 'User.apichannel');
        if (true !== $result) {
            $this->error($result);
        }


        $user = \app\admin\model\User::get($data['id']);

        if (is_null($user)) {
            $this->error('商户不存在!');
        }

        //获取所有接口以及规则
        $apiTypeList = ApiType::getOpenListAndRule();
        $this->assign('api_type_ist', $apiTypeList);

        //获取用户的接口规则
        $userChannelList = UserApichannel::getListByUser($data['id']);

        $this->assign('user_channel_list', $userChannelList);

        $this->assign('user', $user->visible(['merchant_id', 'id'])->toArray());

        return $this->view->fetch();

    }

    public function settlement()
    {

        if ($this->request->isAjax()) {

            $data = $this->request->only(['merchant_id', 'money', 'bankcardId', 'status']);

            $rules = [
                'merchant_id|商户号' => 'require|integer|max:24',
                'money|结算金额' => 'require|float|>:0',
                'bankcardId|银行卡' => 'require|integer|max:12',
                'status|结算状态' => 'require|in:0,1,2'
            ];
            $result = $this->validate($data, $rules);

            if (true !== $result) {
                $this->error($result);
            }

            $userModel = \app\common\model\User::get(['merchant_id' => $data['merchant_id']]);

            if (is_null($userModel)) {
                $this->error('商户不存在！');
            }

            //商户号
            $merchant_id = $userModel['merchant_id'];
            //提多少钱
            $money = $data['money'];
            //用户余额
            $balance = $userModel['money'];
            //冻结金额
            $freezeMoney = $userModel->getFreezeMoney();
            //可用金额
            $userMoney = bcsub($balance, $freezeMoney, 2);
            //手续费
            $commission = $userModel->commission($money);
            $needMoney = bcadd($money, $commission, 2);

            if (!is_numeric($money) || $money <= 0) {
                $this->error('请填写正确的金额');
            }

            if ($needMoney > $userMoney) {
                $this->error('支付金额不足,当前需要' . $needMoney . '元,手续费：' . $commission . '元！');
            }


            // 检查银行卡是否存在
            $bankcardModel = \app\common\model\Bankcard::where([
                'id' => $data['bankcardId'],
                'merchant_id' => $data['merchant_id'],
                'status' => '1'
            ])->find();

            if (is_null($bankcardModel)) {
                $this->error('银行卡无法支付，请更换');
            }

            //获取用户金额的锁
            $redislock = redisLocker();
            $resource = $redislock->lock('pay.' . $merchant_id, 3000);   //单位毫秒


            if ($resource) {
                //开始事务
                Db::startTrans();
                try {
                    $data = [
                        'merchant_id' => $merchant_id,
                        'orderno' => \app\common\model\Pay::createOrderNo(),
                        'style' => '0',
                        'money' => $money,
                        'name' => $bankcardModel['name'],
                        'ka' => $bankcardModel['ka'],
                        'bank' => $bankcardModel['bank'],
                        'province' => $bankcardModel['province'],
                        'city' => $bankcardModel['city'],
                        'zhihang' => $bankcardModel['zhihang'],
                        'status' => $data['status'],
                        'daifustatus' => '0',
                        'charge' => $commission,
                        'req_info' => '',
                        'req_ip' => '后台结算',
                        'createtime' => time()
                    ];

                    $payModel = \app\common\model\Pay::create($data);
                    //更新用户金额
                    $userModel->setInc('withdrawal', $money);
                    //资金变动
                    \app\common\model\User::money(-$needMoney, $userModel->id, '提现：' . $money . '元，手续费：' . $commission . '元', $payModel['orderno'], '2');
                    //写入用户日志表
                    UserLog::addLog($data['merchant_id'], '管理员发起商户提现【' . $data['merchant_id'] . '】【' . $data['money'] . '元】手续费：' . $commission . '元');
                    Db::commit();
                    $redislock->unlock(['resource' => 'pay.' . $merchant_id, 'token' => $resource['token']]);
                } catch (\Exception $e) {
                    Db::rollback();
                    $redislock->unlock(['resource' => 'pay.' . $merchant_id, 'token' => $resource['token']]);
                    $this->error($e->getMessage());
                }
            } else {
                $this->error('系统繁忙，请重试');
            }


            $this->success('结算成功！');
        }
        $id = $this->request->param('id/d', 0);

        $userModel = \app\common\model\User::get($id);

        if (is_null($userModel)) {
            $this->error('商户不存在！');
        }

        $this->assign('merchant_id', $userModel['merchant_id']);
        $this->assign('money', $userModel['money']);
        $this->assign('freezeMoney', $userModel->getFreezeMoney());
        $this->assign('settle', $userModel->settle());
        $this->assign('payrateType', $userModel['payrate_type']);
        $this->assign('payrate', $userModel['payrate']);

        //获取用户的银行卡
        $bankcardList = \app\admin\model\Bankcard::where([
            'merchant_id' => $userModel['merchant_id'],
            'status' => '1'
        ])->field('id,name,ka')->select();

        $bankcardList = collection($bankcardList)->toArray();
        $this->assign('bankcardList', $bankcardList);

        return $this->view->fetch();
    }

}
