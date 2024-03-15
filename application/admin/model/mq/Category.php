<?php

namespace app\admin\model\mq;

use think\Model;


class Category extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'mq_category';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
