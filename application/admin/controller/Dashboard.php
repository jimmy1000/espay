<?php

namespace app\admin\controller;

use app\admin\model\Order;
use app\admin\model\User;
use app\common\controller\Backend;
use fast\Date;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    protected $noNeedRight = ['chart'];
    /**
     * 查看
     */
    public function index()
    {

        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');


        //商户数量
        $totalUser = User::count();
        $totalAgent = User::where(['group_id'=>'2'])->count();

        //成功总订单数
        $totalOrder = Order::where('status','in','1,2')->count();
        //总金额
        $totalOrderAmount = Order::where('status','in','1,2')->sum('total_money');


        //今日资金统计

        $todayOrderCount = Order::whereTime('createtime','today')->count();     //今日订单数
        $todaySuccOrderCount =  Order::whereTime('createtime','today')->where('status','in','1,2')->count();     //今日成功订单数

        $todayMoney = Order::whereTime('createtime','today')->where('status','in','1,2')->sum('total_money');                //今日进账资金
        $todayHaveMoney = Order::whereTime('createtime','today')->where('status','in','1,2')->sum('have_money');            //今日会员金额
        $todayAgentMoney =  Order::whereTime('createtime','today')->where('status','in','1,2')->sum('agent_money');         //今日代理金额
        $todayUpstreamMoney =  Order::whereTime('createtime','today')->where('status','in','1,2')->sum('upstream_money');   //今日上游金额
        $todayExpendMoney = $todayHaveMoney + $todayAgentMoney + $todayUpstreamMoney;                           //今日支出的金额


        //全部订单统计

        $allOrderCount = Order::count();    //全部订单


        $allMoney = Order::where('status','in','1,2')->sum('total_money');
        $allHaveMoney = Order::where('status','in','1,2')->sum('have_money');
        $allAgentMoney =  Order::where('status','in','1,2')->sum('agent_money');
        $allUpstreamMoney =  Order::where('status','in','1,2')->sum('upstream_money');
        $allExpendMoney = $allHaveMoney + $allAgentMoney + $allUpstreamMoney;





        $this->view->assign([
            'totalUser'        => $totalUser,
            'totalAgent'       => $totalAgent,
            'totalOrder'       => $totalOrder,
            'totalOrderAmount' => $totalOrderAmount,
            'todayOrderCount'=>$todayOrderCount,
            'todaySuccOrderCount'=>$todaySuccOrderCount,
            'todayMoney'=>$todayMoney,
            'todayExpendMoney'=>$todayExpendMoney,
            'allOrderCount'=>$allOrderCount,
            'allMoney'=>$allMoney,
            'allExpendMoney'=>$allExpendMoney,

            'totalviews'       => 219390,

            'todayuserlogin'   => 321,
            'todayusersignup'  => 430,
            'todayorder'       => 2324,
            'unsettleorder'    => 132,
            'sevendnu'         => '80%',
            'sevendau'         => '32%',
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'       => $addonVersion,
            'uploadmode'       => $uploadmode
        ]);

        return $this->view->fetch();
    }

    /**
     * 首页订单统计
     */
    public function chart(){

        //获取时间戳
        $end = Date::unixtime('day', 0, 'end');
        $start = Date::unixtime('day', -10);
        $days = [];
        //日期数组
        for($i = 6;$i>=0;$i--){
            $days[] = \date('Y-m-d',Date::unixtime('day', -$i));
        }
        $list = \app\common\model\Order::where([
            'createtime' => ['between', [$start, $end]],
            'status'=>['in','1,2']
        ])->field("COALESCE(sum(`total_money`),0) as `total_amount`,count(id) as `total_orders`,FROM_UNIXTIME(createtime,'%Y-%m-%d') as day")->group('day')->select();

        $list = collection($list)->toArray();
        $list = array_combine(array_column($list,'day'),array_values($list));

        //所有的订单数量
        $allList = \app\common\model\Order::where([
            'createtime' => ['between', [$start, $end]]
        ])->field("count(id) as `total_orders`,FROM_UNIXTIME(createtime,'%Y-%m-%d') as day")->group('day')->select();
        $allList = collection($allList)->toArray();
        $allList = array_combine(array_column($allList,'day'),array_values($allList));



        $totalAmountList = [];
        $totalOrdersList = [];
        foreach ($days as $day){

            if(empty($list[$day])){
                $totalOrdersList[] = 0;
                $totalAmountList[] = 0;
                $totalSuccessRate[] = 0;
            }else{
                $totalOrdersList[] = $list[$day]['total_orders'];
                $totalAmountList[] = $list[$day]['total_amount'];
                $totalSuccessRate[] =  $list[$day]['total_orders'] / $allList[$day]['total_orders'] * 100;
            }
        }



        $result = [
            'totalAmountList'=>$totalAmountList,
            'totalOrdersList'=>$totalOrdersList,
            'totalSuccessRate'=>$totalSuccessRate,
            'days'=>$days
        ];

        $this->success('获取成功。',null,$result);

    }

}
