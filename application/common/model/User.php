<?php

namespace app\common\model;

use Carbon\Carbon;
use fast\Random;
use think\Model;

/**
 * 会员模型
 */
class User extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];


    protected static function init()
    {
        //自动生成商户号和秘钥
        self::afterInsert(function ($row) {
            //生成商户号
            $row->merchant_id = date("Ym") . $row->id;
            //生成商户秘钥
            $row->md5key = Random::alpha(32);
            $row->save();
        });
    }


    /**
     * 商户号生成
     */
    public static function getMerchantId()
    {

        $merchant_id = date('Ymd') . Random::numeric();
    }


    public function parent(){
        $merchant_id = $this->getAttr('merchant_id');
        return User::get(['merchant_id'=>$merchant_id]);
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
     * 提现费率类型
     */
    public function getPayrateTypeAttr($value){
        return $value == '-1' ? config('site.payrate_type') : $value;
    }


    /**
     * 提现费率
     * @param $value
     */
    public function getPayrateAttr($value){
        return $value < 0 ? config('site.payrate') : $value;
    }


    /**
     * 结算信息
     */
    public function settle(){
        return $this->getAttr('balancestyle').'+'.$this->getAttr('balancetime');
    }

    /**
     * 代付手续费
     */
    public function commission($money){
        $payRateType = $this->getAttr('payrate_type');
        $payRate = $this->getAttr('payrate');   //提现费率
        //百分比
        if($payRateType == '1'){
            $payRate = bcmul($money,$payRate,2);
            $payRate = bcdiv($payRate,100,2);
        }
       return $payRate;

    }

    /**
     * 获取用户的冻结金额
     */
    public function getFreezeMoney(){
        $money =  Order::getFrozenMoney($this->getAttr('merchant_id'),$this->settle());
        return $money;
    }




    /**
     * 获取个人URL
     * @param string $value
     * @param array $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }

    public function getCreatetimeAttr($value)
    {

        return Carbon::createFromTimestamp($value)->toDateTimeString();
    }

    /**
     * 获取头像
     * @param string $value
     * @param array $data
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {

        if (!$value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';

            $value = letter_avatar($data['nickname']);
            //查看是否认证，如果认证则获取认证的姓名
            if (!is_null($this->auth) && $this->auth->status == 1) {
                $value = letter_avatar($this->auth->name);
            }

        }
        return $value;
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {

        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param string $value
     * @param array $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object)$value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 变更会员余额
     * @param int $money 余额
     * @param int $user_id 会员ID
     * @param string $memo 备注
     * @param string $orderno 商户订单号
     */
    public static function money($money, $user_id, $memo, $orderno, $style = '1')
    {
        $user = self::get($user_id);
        if ($user && $money != 0) {
            $before = $user->money;
            $after = $user->money + $money;
            //更新会员信息
            $user->save(['money' => $after]);
            //写入日志
            MoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo, 'orderno' => $orderno, 'style' => $style]);
        }
    }

    /**
     * 变更会员积分
     * @param int $score 积分
     * @param int $user_id 会员ID
     * @param string $memo 备注
     */
    public static function score($score, $user_id, $memo)
    {
        $user = self::get($user_id);
        if ($user && $score != 0) {
            $before = $user->score;
            $after = $user->score + $score;
            $level = self::nextlevel($after);
            //更新会员信息
            $user->save(['score' => $after, 'level' => $level]);
            //写入日志
            ScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value) {
            if ($score >= $value) {
                $level = $key;
            }
        }
        return $level;
    }


    /**
     * 重新设置提现密码
     * @param $password
     */
    public static function setPayPassword($password, $user_id)
    {
        $user = self::get($user_id);
        $salt = \fast\Random::alnum();
        $user->paysalt = $salt;
        $user->paypassword = \app\common\library\Auth::instance()->getEncryptPassword($password, $salt);
        //只允许写入指定字段
        return $user->save();
    }

    /**
     * 验证支付密码是否输入正确
     * @param $password
     * @param $user_id
     */
    public static function verifyPayPassword($password, $user_id)
    {
        $user = self::get($user_id);
        $password = \app\common\library\Auth::instance()->getEncryptPassword($password, $user->paysalt);
        return $password == $user->paypassword;
    }

    /**
     * 重新设置登录密码
     * @param $password
     */
    public static function setPassword($password, $user_id)
    {
        $user = self::get($user_id);
        $salt = \fast\Random::alnum();
        $user->salt = $salt;
        $user->password = \app\common\library\Auth::instance()->getEncryptPassword($password, $salt);
        //只允许写入指定字段
        return $user->save();
    }

    /**
     * 验证登录密码是否输入正确
     * @param $password
     * @param $user_id
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function verifyPassword($password, $user_id)
    {
        $user = self::get($user_id);
        $password = \app\common\library\Auth::instance()->getEncryptPassword($password, $user->salt);
        return $password == $user->password;
    }


    /**
     * 设置google密钥绑定
     * @param $secret
     * @param $user_id
     */
    public static function setGoogleSecret($secret, $user_id)
    {
        $user = self::get($user_id);
        $user->googlesecret = $secret;
        $user->googlebind = 1;
        return $user->save();
    }

    /**
     * 清除用户的google密钥绑定
     * @param $user_id
     */
    public static function clearGoogleSecret($user_id)
    {
        $user = self::get($user_id);
        $user->googlesecret = '';
        $user->googlebind = 0;
        return $user->save();
    }

    /**
     * 清除用户的手机号绑定
     * @param $user_id
     * @return false|int
     * @throws \think\exception\DbException
     */
    public static function clearMobileBind($user_id)
    {
        $user = self::get($user_id);
        $user->mobilebind = 0;
        return $user->save();
    }

    /**
     * 获取用户的认证信息
     */
    public function auth()
    {
        return $this->hasOne('UserAuth', 'user_id');
    }

    /**
     * 资金变动表
     */
    public function moneylog()
    {
        return $this->hasMany('MoneyLog', 'user_id', 'id');
    }


    /**
     * 获取用户可用的接口以及费率
     */
    public function getApiList()
    {

        $user_id = $this->getAttr('id');
        //获取系统的所有开启的通道
        $api_type_list = ApiType::getOpenList();
        //获取用户的接口规则
        $api_user_channels = UserApichannel::getListByUser($user_id);
        $result = [];   //结果数组
        foreach ($api_type_list as $type) {
            $type_id = $type['id'];
            $rule_id = $type['api_rule_id'];
            $rate_flag = false;
            //如果用户拥有默认的规则
            if (!empty($api_user_channels[$type_id])) {
                $rule_id = $api_user_channels[$type_id]['api_rule_id'] == 0 ? $rule_id : $api_user_channels[$type_id]['api_rule_id'];
                $rate_flag = $api_user_channels[$type_id]['rate'] > 0 ? true : false;
                //如果通道关闭了
                if ($api_user_channels[$type_id]['status'] == 0) {
                    continue;
                }
            }
            //没有设置规则的情况下
            if ($rule_id == 0) {
                continue;
            }
            $channel_info = ApiRule::getChannelInfo($rule_id);
            //没有设置的规则的时候不可用
            if(empty($channel_info)){
                continue;
            }
            if ($rate_flag) {
                $channel_info['rate_list'] = [$api_user_channels[$type_id]['rate']];
            }
            $type_text = '';
            switch ($channel_info['info']['type']) {
                case '0':
                    $type_text = '单通道模式';
                    break;
                case '1':
                    $type_text = '顺序轮询';
                    break;
                case '2':
                    $type_text = '随机轮询';
                    break;
            }
            //封装结果
            $result[] = [
                'name' => $type['name'],  //接口名称
                'id' => $type['id'],
                'code' => $type['code'],  //调用代码
                'rule_type' => $channel_info['info']['type'], //规则
                'rule_type_text' => $type_text,
                'rate' => implode(',', $channel_info['rate_list']), //费率数组
                'money_range' => implode(',', $channel_info['money_range_list']),   //充值范围
                'total' => $channel_info['total'],    //每天额度
                'has' => $channel_info['has']         //已用额度
            ];

        }
        return $result;

    }


    /**
     * 获取用户可用的接口以及费率
     * 以接口类型为key
     */
    public function getApiList2($money=0)
    {

        $user_id = $this->getAttr('id');
        //获取系统的所有开启的通道
        $api_type_list = ApiType::getOpenList();
        //获取用户的接口规则
        $api_user_channels = UserApichannel::getListByUser($user_id);
        $result = [];   //结果数组
        foreach ($api_type_list as $type) {
            $type_id = $type['id'];
            $rule_id = $type['api_rule_id'];
            $rate_flag = false;
            $user_rate = 0;

            //如果用户拥有默认的规则
            if (!empty($api_user_channels[$type_id])) {
                $rule_id = $api_user_channels[$type_id]['api_rule_id'] == 0 ? $rule_id : $api_user_channels[$type_id]['api_rule_id'];
                $rate_flag = $api_user_channels[$type_id]['rate'] > 0 ? true : false;
                //如果通道关闭了
                if ($api_user_channels[$type_id]['status'] == 0) {
                    continue;
                }
            }
            //没有设置规则的情况下
            if ($rule_id == 0) {
                continue;
            }

            //轮询规则自动失效限额的接口
            $channel_info = ApiRule::getChannelInfo($rule_id, false,true,$money);

            //没有设置的规则的时候不可用
            if(empty($channel_info)){
                continue;
            }


            //如果用户自定义了费率
            if ($rate_flag) {
                $user_rate = $api_user_channels[$type_id]['rate'];
            }
            $type_text = '';
            switch ($channel_info['info']['type']) {
                case '0':
                    $type_text = '单通道模式';
                    break;
                case '1':
                    $type_text = '顺序轮询';
                    break;
                case '2':
                    $type_text = '随机轮询';
                    break;
            }
            //封装结果
            $result[$type['code']] = [
                'id' => $type['id'],
                'name' => $type['name'],  //接口名称
                'code' => $type['code'],  //调用代码
                'domain' => $type['domain'],  //接口域名
                'account_id' => $channel_info['info']['api_account_ids']['id'],
                'account_weight' => $channel_info['info']['api_account_ids']['weight'],
                'rule_type' => $channel_info['info']['type'], //规则
                'rule_type_text' => $type_text,
                'rate' => $channel_info['rate_list'], //费率数组
                'user_rate' => $user_rate,
                'money_range' => implode(',', $channel_info['money_range_list']),   //充值范围
                'total' => $channel_info['total'],    //每天额度
                'has' => $channel_info['has']         //已用额度
            ];

        }
        return $result;

    }

    /**
     * 获取用户的下级列表
     * @param $merchant_id
     */
    public static function getChildList($merchant_id){
        $user_list = self::where('agent_id', $merchant_id)->field(['merchant_id', 'id', 'bio', 'money','withdrawal', 'prevtime', 'createtime', 'username', 'group_id', 'status', 'agent_id'])->select();
        return $user_list;
    }
    /**
     * 获取指定代理的下级 只获取两级 系统最多支持三级分销 本身算一级
     * @param $agent_id
     * @param int $level
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getListByAgent($agent_id, $level = 0)
    {


        if ($level == 9) {
            return [];
        }
        
        ++$level;

        $user_list = self::where('agent_id', $agent_id)->field(['merchant_id', 'id', 'bio', 'money', 'prevtime', 'createtime', 'username', 'group_id', 'status', 'agent_id'])->select();

        //如果存在的话获取下一级 不存在直接返回空数组
        if (count($user_list) > 0) {
            $user_list = collection($user_list)->toArray();
            foreach ($user_list as $user) {
                $agent_list = self::getListByAgent($user['merchant_id'], $level);
                if ($agent_list) {
                    $user_list = array_merge($user_list, $agent_list);
                }
            }
            return $user_list;
        }
        return [];
    }

    /**
     * 获取某个商户的上级列表
     * @param $user_id
     * @param int $level
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public static function getAgentIds($user_id, $level = 0)
    {

        $user = self::get($user_id);
        $result = [];



        if ($level == 9) {
            return false;
        }
        ++$level;

        if (!is_null($user) && $user['agent_id'] != 0) {
            $result[] = $user['agent_id'];
            $superior = self::getAgentIds($user['agent_id'], $level);
            if ($superior) {
                $result = array_merge($result, $superior);
            }
            return $result;
        }
        return false;
    }

    /**
     * 获取代理的某个通道的费率
     * @param $user_id
     * @param $api_type_id
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function getAgentRate($user_id, $api_type_id)
    {

        $userChannelModel = UserApichannel::get([
            'user_id' => $user_id,
            'api_type_id' => $api_type_id,
//            'status' => '1'       无论代理开启不开启通道都给分红
        ]);

        //代理必须要设置费率
        if (is_null($userChannelModel) || $userChannelModel['rate'] <= 0) {
            return false;
        }

        return $userChannelModel['rate'];
    }

}
