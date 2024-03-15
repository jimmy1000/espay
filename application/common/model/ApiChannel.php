<?php
/**
 * ApiChannel.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-04-24
 */

namespace app\common\model;

use Carbon\Carbon;
use think\Model;

class ApiChannel extends Model
{

    protected $name = 'api_channel';


    public function account()
    {
        return $this->belongsTo('ApiAccount', 'api_account_id', 'id');
    }

    public function apitype()
    {
        return $this->belongsTo('ApiType', 'api_type_id', 'id');
    }


    protected function getTodaymoneyAttr($value)
    {
        $today = Carbon::now()->toDateString();
        if($this->getData('today')!=$today){
            return 0 ;
        }
        return $value;
    }

    protected function setTodaymoneyAttr($value,$row){
        $today = Carbon::now()->toDateString();

        if($this->getData('today')!=$today){
            $this->save([
                'today'=>$today
            ]);
        }
        return $value;
    }

    /**
     * 获取某个账户的通道
     * @param $account_id
     */
    public static function getChannelByAccount($account_id)
    {

        $list = self::all(function ($query) use ($account_id) {
            $query->where('api_account_id', $account_id);
        });

        if (count($list) > 0) {
            $list = collection($list)->toArray();
            $type_list = array_column($list, 'api_type_id');
            $list = array_combine($type_list, $list);
            return $list;
        }
        return [];

    }


    /**
     * 根据接口类型获取所有开启的接口账户
     * @param $typeid
     * @return ApiChannel[]|array|false
     * @throws \think\exception\DbException
     */
    public static function getAccountByType($typeid,$open = true)
    {

        $list = self::all(function ($query) use ($typeid,$open) {
            $query->where('api_type_id', $typeid);

            if($open){
                $query->where('status', 1);
            }

        }, ['account' => function ($query) {
            $query->field('id,name');
        }]);

        if (count($list) > 0) {
            $list = collection($list)->toArray();
            $list = array_combine(array_column($list, 'api_account_id'), array_values($list));
            return $list;
        }
        return [];
    }

}