<?php
/**
 * Xiaoxiong.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-07-11
 */
namespace app\common\api;

class Xiaoxiong {


    public function pay($params)
    {
        return [1,'http://www.163.com'];
    }


    public function notify()
    {

        redisLocker();
    }
}