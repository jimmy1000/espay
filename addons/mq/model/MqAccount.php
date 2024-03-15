<?php
/**
 * MqOrder.php
 * 易聚合支付系统
 * =========================================================

 * ----------------------------------------------
 *
 *
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-06-09
 */

namespace addons\mq\model;

use Carbon\Carbon;
use think\Model;


class MqAccount extends Model
{
    protected $name = 'mq_account';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';


    /**
     * 获取可以使用的类型
     * @param $type
     */
    public static function getList($type)
    {
        $accountList = MqAccount::where([
            'status' => '1',  //开启
            'type' => $type,  //类型
        ])->select();

        return collection($accountList)->toArray();
    }

    /**
     * 随机获取一个账户
     */
    public static function getAccount($type)
    {


        $accountList = self::getList($type);

        if(empty($accountList)){
            return false;
        }

        while (true) {

            $key = array_rand($accountList);
            //随机取出一条
            $account = $accountList[$key];
            if ($account['maxmoney'] > 0 && $account['todaymoney'] >= $account['maxmoney']) {

                unset($accountList[$key]);
                $accountList = array_values($accountList);      //重排索引
                continue;
            }
            break;
        }

        return $account;


    }


    protected function getTodaymoneyAttr($value)
    {
        $today = Carbon::now()->toDateString();
        if ($this->getData('today') != $today) {
            return 0;
        }
        return $value;
    }

    protected function setTodaymoneyAttr($value, $row)
    {
        $today = Carbon::now()->toDateString();

        if ($this->getData('today') != $today) {
            $this->save([
                'today' => $today
            ]);
        }
        return $value;
    }
}