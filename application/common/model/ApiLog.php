<?php

/**
 * ApiLog.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-04-24
 */

namespace app\common\model;

use think\Model;

class ApiLog extends Model
{

    

    // 表名
    protected $name = 'api_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    public function getContentAttr($value){
        return unserialize($value);
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }



    public static function log($data, $msg = '',$result=''){

        $ip = request()->ip();
        $http = empty($_SERVER['HTTP_REFERER'])? '' : $_SERVER['HTTP_REFERER'];


        $content = serialize($data);
        $status = 0;
        if (empty($msg)) {
            $status = 1;
            $msg = $result;
        }
        $data = [
            'merchant_id'=>empty($data['merId']) ? '0' : $data['merId'],
            'http'=>$http,
            'content'=>$content,
            'result'=>$msg,
            'status'=>$status,
            'orderno'=>empty($data['orderId']) ? '0' : $data['orderId'],
            'total_money'=>empty($data['orderAmt']) ? '0' : $data['orderAmt'],
            'channel'=>empty($data['channel']) ? '0' : $data['channel'],
            'ip'=>$ip
        ];

        self::create($data);
    }

}
