<?php

namespace app\admin\model;

use think\Model;


class NotifyLog extends Model
{

    

    // 表名
    protected $name = 'notify_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    

   public function myorder(){
       return $this->belongsTo('Order','order_id','id','','LEFT')->setEagerlyType(0);
   }


}
