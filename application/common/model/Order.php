<?php
/**
 * Order.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-04-29
 */

namespace app\common\model;

use fast\Date;
use think\Model;


class Order extends Model
{


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
        'createtime_text'
    ];

    public static function createOrderNo()
    {
        $order_sn = create_orderno();
        if (!is_null(self::get(['sys_orderno' => $order_sn]))) {
            $order_sn = self::createOrderNo();
        }
        return $order_sn;
    }


    protected function getReqInfoAttr($value)
    {

        $req = [];
        parse_str($value, $req);

        return $req;
    }

    public function getPaytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['paytime']) ? $data['paytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['createtime']) ? $data['createtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
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

    public function getStyleList()
    {
        return ['0' => '普通订单', '1' => '充值订单'];
    }

    public function getStatusList()
    {
        return ['0' => '未支付', '1' => '已成功', '2' => '扣量订单'];
    }

    public function getNotifyStatusList()
    {
        return ['0' => '未通知', '1' => '通知失败', '2' => '通知成功'];
    }


    public function apiaccount()
    {
        return $this->belongsTo('ApiAccount', 'api_account_id', 'id', '', 'LEFT')->setEagerlyType(0);
    }

    public function user()
    {
        return $this->belongsTo('User', 'merchant_id', 'merchant_id', 'LEFT')->setEagerlyType(0);
    }

    public function apitype()
    {
        return $this->belongsTo('ApiType', 'api_type_id', 'id', '', 'LEFT')->setEagerlyType(0);
    }

    public function upstream()
    {
        return $this->belongsTo('ApiUpstream', 'api_upstream_id', 'id', '', 'LEFT')->setEagerlyType(0);

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
        //继续计算下级，更改支持十级分销
        if (10 > $data['level'] && $userModel['agent_id'] > 0) {
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

    /**
     * 订单channel
     */
    public function channel()
    {

        return ApiChannel::get([
            'api_account_id' => $this->getAttr('api_account_id'),
            'api_type_id' => $this->getAttr('api_type_id')
        ]);

    }

    /**
     * 获取用户冻结的金额
     * @param $merchant_id
     * @param $settle
     */
    public static function getFrozenMoney($merchant_id, $settle)
    {

        $balancestyle = $settle;
        $userModel = User::get([
            'merchant_id' => $merchant_id
        ]);


        //获取最近几天的收入金额
        $t = 0;
        $kl = 0; //为1冻结百分比计算
        if (strstr($balancestyle, 'T') !== false) {
            $t = str_replace('T+', '', $balancestyle);
            $ga = date("w");
            if ($ga == 0)
                $t = $t + 2;
            if ($ga == 6)
                $t = $t + 1;
        } elseif (strstr($balancestyle, 'D') !== false) {
            $t = str_replace('D+', '', $balancestyle);
        } else {
            return false;
        }

        //不冻结的情况 返回0
        if ($t == 0) {
            $paylv = 0;
            if (!empty($userModel['paylv']) && is_numeric($userModel['paylv']) && $userModel['paylv'] > 0) {
                if ($userModel['paylv'] > 100) $userModel['paylv'] = 100;
                $paylv = $userModel['paylv'];
            }
            $kl = 1;
            $t = 1;
            if ($paylv == 100) return 0;
        }


        $time = strtotime(date('Y-m-d')) - 24 * 3600 * ($t - 1);

        //结算订单金额
        $where = [
            'style' => '0',
            'status' => '1',
            'merchant_id' => $merchant_id,
            'paytime' => ['>=', $time]
        ];

        $money = self::where($where)->sum('have_money');

        //是否冻结当天的多少金额
        if ($kl == 1 && $money > 0 && $paylv > 0) {
            $money = $money * (100 - $paylv) / 100;
        }
        $agentMoney = 0;
        //代理金额冻结
        if ($userModel['group_id'] == '2') {
            $agentWhere = [
                'merchant_id' => $merchant_id,
                'createtime' => ['>=', $time]
            ];
            $agentMoney = OrderAgent::where($agentWhere)->sum('money');
            if ($kl == 1 && $agentMoney > 0 && $paylv > 0) {
                $agentMoney = $agentMoney * (100 - $paylv) / 100;
            }

        }


        return $money + $agentMoney;


    }

}