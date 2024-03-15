<?php

namespace app\admin\model;

use app\common\model\MoneyLog;
use app\common\model\Order;
use app\common\model\PayOrder;
use fast\Random;
use think\Db;
use think\Model;

class User extends Model
{

    // 表名
    protected $name = 'user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'prevtime_text',
        'logintime_text',
        'jointime_text',
        'settle'

    ];

    public function getOriginData()
    {
        return $this->origin;
    }

    protected static function init()
    {

        //添加的时候也会执行这个方法
        self::beforeUpdate(function ($row) {
            $changed = $row->getChangedData();

            //如果有修改密码
            if (isset($changed['password'])) {
                if ($changed['password']) {
                    $salt = \fast\Random::alnum();
                    $row->password = \app\common\library\Auth::instance()->getEncryptPassword($changed['password'], $salt);
                    $row->salt = $salt;
                } else {
                    unset($row->password);
                }
            }
            // 修改支付密码
            if (isset($changed['paypassword'])) {
                if ($changed['paypassword']) {
                    $salt = \fast\Random::alnum();
                    $row->paypassword = \app\common\library\Auth::instance()->getEncryptPassword($changed['paypassword'], $salt);
                    $row->paysalt = $salt;
                } else {
                    unset($row->paypassword);
                }
            }
        });


        self::beforeUpdate(function ($row) {
            $changedata = $row->getChangedData();
            if (isset($changedata['money'])) {
                $origin = $row->getOriginData();
                MoneyLog::create(['user_id' => $row['id'], 'money' => $changedata['money'] - $origin['money'], 'before' => $origin['money'], 'after' => $changedata['money'], 'memo' => '管理员变更金额']);
            }
        });


        //开户时生成商户号和密码
        self::beforeInsert(function ($row){
            //生成md5秘钥
            $row->md5key = Random::alpha(32);
            $add_time = time();
            if(!isset($row->jointime)){
                $row->jointime = $add_time;
            }
            if(!isset($row->joinip)){
                $row->joinip = '0.0.0.0';       //表示由管理员加入
            }
        });


        //自动生成商户号和秘钥
        self::afterInsert(function ($row){
            //生成商户号
            $row->merchant_id = date("Ym").$row->id;
            $row->save();
        });


        //删除关联数据
        self::afterDelete(function ($row){
            $user_id = $row->id;
            $merchant_id = $row->getData('merchant_id');
            //删除关联表中的数据
            UserAuth::destroy(['user_id'=>$user_id]);
            UserLog::destroy(['merchantid'=>$merchant_id]);
            UserApichannel::destroy(['user_id'=>$user_id]);
            //删除对应的订单数据
            Order::destroy(['merchant_id'=>$merchant_id]);
            OrderAgent::destroy(['merchant_id'=>$merchant_id]);
            //删除对应的提现数据
            Pay::destroy(['merchant_id'=>$merchant_id]);
            MoneyLog::destroy(['user_id'=>$user_id]);
        });
    }

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['prevtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['logintime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['jointime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPrevtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setLogintimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setJointimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function group()
    {
        return $this->belongsTo('UserGroup', 'group_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }



    /**
     * 重新设置系统密钥
     * @param $userid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function resetMd5Key($userid)
    {
        return self::update([
            'md5key'=>Random::alpha(32)
        ],[
            'id'=>$userid
        ]);
    }




    /**
     * 代理收益
     * @param $value
     * @return mixed
     */
    public function getIfagentmoneyAttr($value)
    {
        return $value == '-1' ? config('site.ifagentmoney') : $value;
    }

    /**
     * 结算类型
     * @param $value
     */
    public function getBalancestyleAttr($value){
        return $value == '-1' ? config('site.balancestyle') : $value;
    }

    /**
     * 结算周期
     * @param $value
     * @return mixed
     */
    public function getBalancetimeAttr($value){
        return $value == '-1' ? config('site.balancetime') : $value;
    }

    /**
     * 当日提现比例
     * @param $value
     * @return mixed
     */
    public function getPaylvAttr($value){
        return $value == '-1' ? config('site.paylv') : $value;
    }

    /**
     * 结算信息
     */
    public function getSettleAttr(){
        return $this->getAttr('balancestyle').'+'.$this->getAttr('balancetime');
    }

    /**
     * 获取用户的冻结金额
     */
    public function getFreezeMoney(){
        $money =  Order::getFrozenMoney($this->getAttr('merchant_id'),$this->settle());
        if(!$money){
            return $this->getAttr('money');
        }
        return $money;
    }


    public function getWithdrawalAttr($value){
        if(floatval($value)<0){
            return 0;
        }
        return $value;
    }



}
