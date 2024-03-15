<?php
/**
 * RepayNotify.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-13
 */
namespace app\common\job;

use addons\faqueue\model\FaqueueLog;
use app\common\model\NotifyLog;
use app\common\model\Pay;
use app\common\model\User;
use fast\Http;
use fast\Random;
use fast\Rsa;
use think\Db;
use think\Log;
use think\queue\Job;

class RepayNotify{


    /**
     * fire方法是消息队列默认调用的方法
     * @param Job $job 当前的任务对象
     * @param array|mixed $data 发布任务时自定义的数据
     */

    public function fire(Job $job, $data)
    {


        $pay_id = $data['pay_id'];

        $payModel = Pay::get($pay_id);

        if (is_null($payModel)) {
            return $job->delete();
        }

        //普通订单和没有异步地址的订单不通知
        if($payModel['style'] == '0' || empty($payModel['req_info']['notifyUrl'])){
            return $job->delete();
        }

        //通知成功或者通知过5次了
        if ($payModel->notify_status == '2' || $payModel->notify_count >= 5) {

            return $job->delete();
        }

        Db::startTrans();

        try {
            (new FaqueueLog())->log($job->getQueue(), $job->getName(), $data);

            $orderno = substr($payModel->orderno, strlen($payModel->merchant_id));

            //发送通知
            $post_data = [
                'merId' => $payModel->merchant_id,          //商户号
                'orderId' => $orderno,                       //商户订单号
                'money'=>$payModel['money'],                 //金额
                'status' => $payModel->status,              //通知状态 1为支付成功
                'nonceStr' => Random::alnum('32')        //随机字符串

            ];

            if (!empty($payModel->req_info['attch'])) {
                $post_data['attch'] = $payModel->req_info['attch'];       //附加信息
            }

            $userModel = User::get(['merchant_id'=>$payModel['merchant_id']]);

            $post_data['sign'] = makeApiSign($post_data, $userModel->md5key, config('site.private_key'));

            $notifyUrl = $payModel->req_info['notifyUrl'];

            // 控制台显示消息
            $msg = date("Y-m-d H:i:s").'代付异步通知发送:订单号-》》'.$payModel['orderno'].',通知地址：'.$notifyUrl;
            echo $msg;
            Log::record($msg,'REPAY_NOTIFY');

            $result = Http::post($notifyUrl, $post_data);

            // 写入通知日志表
            NotifyLog::log($payModel->id,$notifyUrl,$post_data,$result);

            if ($result == 'success') {
                //更改订单状态
                $payModel->notify_status = '2';
                $payModel->notify_count = $payModel->notify_count + 1;
                $job->delete();
            }else{
                //更改订单状态
                $payModel->notify_status = '1';
                $payModel->notify_count = $payModel->notify_count + 1;
                //延迟五秒执行
                $job->release(5);
            }
            $payModel->save();
            Db::commit();

        } catch (\Exception $e) {
            echo $e->getMessage();
            Db::rollback();
            $job->release(5);
        }
    }



}