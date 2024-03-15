<?php
/**
 * Bank.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-08
 */
namespace  app\common\api;

class Bank  extends Base{


    public function pay($params)
    {
//        return [0,$params['bankcode']];
       return [1,'http://cn.unionpay.com/'];
    }

    public function notify()
    {
        // TODO: Implement notify() method.
    }
}