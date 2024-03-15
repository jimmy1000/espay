<?php

namespace app\admin\model\finance;

use app\admin\model\User;
use think\Model;


class Moneylog extends Model
{


    // 表名
    protected $name = 'user_money_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function user(){
        return $this->belongsTo('app\admin\model\User','user_id','id','','LEFT')->setEagerlyType(0);
    }
    

    







}
