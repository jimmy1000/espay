<?php
/**
 * Notify.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-04
 */

namespace app\common\job;

use addons\faqueue\model\FaqueueLog;
use app\common\model\NotifyLog;
use app\common\model\Order;
use Carbon\Carbon;
use fast\Http;
use fast\Random;
use fast\Rsa;
use think\Db;
use think\Log;
use think\queue\Job;

class Notify
{

    /**
     * fire方法是消息队列默认调用的方法
     * @param Job $job 当前的任务对象
     * @param array|mixed $data 发布任务时自定义的数据
     */

    public function fire(Job $job, $data)
    {


        $order_id = $data['order_id'];

        //如果有并发通知过来只处理一个

        //同一时刻 同一用户只能处理一个
        $redislock = redisLocker();
        $resource = $redislock->lock('notify.' . $order_id, 3000);   //单位毫秒

        //说明有订单在处理
        if(!$resource){
            return $job->delete();
        }


        $orderModel = Order::get($order_id);

        if (is_null($orderModel)) {
            return $job->delete();
        }

        //通知成功或者通知过5次了
        if ($orderModel->notify_status == '2' || $orderModel->notify_count >= 5) {

            return $job->delete();
        }


        Db::startTrans();

        try {
            (new FaqueueLog())->log($job->getQueue(), $job->getName(), $data);

            $orderno = substr($orderModel->orderno, strlen($orderModel->merchant_id));

            //发送通知
            $post_data = [
                'merId' => $orderModel->merchant_id,          //商户号
                'orderId' => $orderno,            //商户订单号
                'sysOrderId' => $orderModel->sys_orderno,     //系统订单号
                'desc' => $orderModel->req_info['desc'],      //描述
                'orderAmt' => $orderModel->total_money,       //订单金额
                'status' => $orderModel->status,              //通知状态 1为支付成功
                'nonceStr' => Random::alnum('32')        //随机字符串

            ];

            if (!empty($orderModel->req_info['attch'])) {
                $post_data['attch'] = $orderModel->req_info['attch'];       //附加信息
            }

            $userModel = $orderModel->user;

            $post_data['sign'] = makeApiSign($post_data, $userModel->md5key, config('site.private_key'));

            $notifyUrl = $orderModel->req_info['notifyUrl'];


            // 控制台显示消息
            $msg = date("Y-m-d H:i:s").'异步通知发送:订单号-》》'.$orderModel['orderno'].',通知地址：'.$notifyUrl;
            echo $msg;
            Log::record($msg,'NOTIFY');

            $result = Http::post($notifyUrl, $post_data);

            // 写入通知日志表
            NotifyLog::log($orderModel->id,$notifyUrl,$post_data,$result);

            if ($result == 'success') {
                //更改订单状态
                $orderModel->notify_status = '2';
                $orderModel->notify_count = $orderModel->notify_count + 1;
                $job->delete();
            }else{
                //更改订单状态
                $orderModel->notify_status = '1';
                $orderModel->notify_count = $orderModel->notify_count + 1;
                //延迟五秒执行
                $job->release(5 * $orderModel->notify_count);
            }
            $orderModel->save();
            Db::commit();

        } catch (\Exception $e) {
            echo $e->getMessage();
            Db::rollback();
            $job->release(5 * $orderModel->notify_count);
        }finally {
            $redislock->unlock(['resource' => 'notify.' . $order_id, 'token' => $resource['token']]);
        }

    }

}