<?php
/**
 * Notify.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-19
 */
namespace easypay;

use addons\goeasy\library\Goeasy;

class Notify {


    static $goeasy = null;


    public static function getGoeasy(){
        if (is_null(self::$goeasy)){
            self::$goeasy = new Goeasy();
        }
        return self::$goeasy;
    }

    /**
     * 代付订单通知
     */
    public static function repay(){
        $adminIds = explode(',',config('site.repay_notify_adminid'));
        if (!empty($adminIds)){
            foreach ($adminIds as $adminId){
                self::getGoeasy()->sendToAdmin($adminId, '您有新的代付订单，请及时处理。');
            }
        }
    }

    /**
     * 银行卡账户审核
     */
    public static function bankcard(){

        //如果系统没有开启银行卡审核
        if(config('site.ifcheckka') != '1'){
            return;
        }
        $adminIds = explode(',',config('site.repay_notify_adminid'));
        if (!empty($adminIds)){
            foreach ($adminIds as $adminId){
                self::getGoeasy()->sendToAdmin($adminId, '您有新的提现银行卡待审核。');
            }
        }

    }
}