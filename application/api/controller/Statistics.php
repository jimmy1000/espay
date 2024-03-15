<?php
/**
 * Statistics.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-09
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\ApiAccount;
use app\common\model\ApiChannel;
use app\common\model\ApiLog;
use app\common\model\ApiRule;
use app\common\model\ApiType;
use app\common\model\Bank;
use app\common\model\MoneyLog;
use app\common\model\Order;
use app\common\model\User;
use Carbon\Carbon;
use fast\Date;
use fast\Random;
use think\Cache;
use think\Db;
use think\Log;

class Statistics extends Api {

    protected $noNeedLogin = [];

    //不需要权限检查的方法
    protected $noNeedRight = ['*'];


    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 首页统计信息
     */
    public function index(){

        $data = [];
        $userModel = $this->auth->getUser();

        //用户余额
        $data['userMoney'] = $userModel['money'];
        //用户冻结金额
        $data['userFreezeMoney'] =$userModel->getFreezeMoney();

        //今日交易量
        $data['todayMoney'] = Order::whereTime('createtime','today')->where('merchant_id',$this->auth->merchant_id)->sum('total_money');
        //成功量
        $data['todaySuccMoney'] = Order::whereTime('createtime','today')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->sum('total_money');


        $count = \app\common\model\Order::whereTime('createtime','today')->where('merchant_id',$this->auth->merchant_id)->count();

        $succCount = \app\common\model\Order::whereTime('createtime','today')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->count();

        if($count == 0){
            $data['successRate'] = 100;
        }else{
            $data['successRate'] =  number_format($succCount / $count * 100,2);
        }


        $this->success('获取成功。',$data);

    }


    /**
     * 首页统计信息2
     */
    public function index2(){
        $data = [];
        $userModel = $this->auth->getUser();

        //今日订单多少笔
        $data['todayCount'] = \app\common\model\Order::whereTime('createtime','today')->where('merchant_id',$this->auth->merchant_id)->count();
        //成功多少笔
        $data['todaySuccCount'] = \app\common\model\Order::whereTime('createtime','today')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->count();

        //昨日订单
        $data['yesCount'] = \app\common\model\Order::whereTime('createtime','yesterday')->where('merchant_id',$this->auth->merchant_id)->count();
        //成功多少笔
        $data['yesSuccCount'] = \app\common\model\Order::whereTime('createtime','yesterday')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->count();

        //今日收益
        $data['today'] = \app\common\model\Order::whereTime('createtime','today')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->sum('have_money');
        //昨日收益
        $data['yesterday'] = \app\common\model\Order::whereTime('createtime','yesterday')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->sum('have_money');;


        //今日实收
        $data['todayMoney'] = MoneyLog::whereTime('createtime','today')->where('style','in',['1','3'])->where('user_id',$this->auth->id)->sum('money');
        $data['yesterdayMoney'] = MoneyLog::whereTime('createtime','yesterday')->where('style','in',['1','3'])->where('user_id',$this->auth->id)->sum('money');


        $this->success('获取成功。',$data);

    }

    /**
     * 首页图表 7天金额
     */
    public function orderChart(){


        //获取时间戳
        $end = Date::unixtime('day', 0, 'end');
        $start = Date::unixtime('day', -10);
        $days = [];
        //日期数组
        for($i = 6;$i>=0;$i--){
            $days[] = \date('Y-m-d',Date::unixtime('day', -$i));
        }
        $list = Order::where([
            'createtime' => ['between', [$start, $end]],
            'status'=>'1',
            'merchant_id'=>$this->auth->merchant_id
        ])->field("COALESCE(sum(`total_money`),0) as `total_amount`,count(id) as `total_orders`,FROM_UNIXTIME(createtime,'%Y-%m-%d') as day")->group('day')->select();

        $list = collection($list)->toArray();
        $list = array_combine(array_column($list,'day'),array_values($list));


        $totalAmountList = [];
        $totalOrdersList = [];
        foreach ($days as $day){

            if(empty($list[$day])){
                $totalOrdersList[] = 0;
                $totalAmountList[] = 0;
            }else{
                $totalOrdersList[] = $list[$day]['total_orders'];
                $totalAmountList[] = $list[$day]['total_amount'];
            }
        }



        $result = [
            'totalAmountList'=>$totalAmountList,
            'totalOrdersList'=>$totalOrdersList,
            'days'=>$days
        ];

        $this->success('获取成功。',$result);

    }




}