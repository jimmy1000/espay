<?php

namespace app\common\model;

use think\Model;


class Bank extends Model
{

    

    // 表名
    protected $name = 'bank';
    
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
    

    public function getStatusList()
    {
        return ['0' => '关闭', '1' => '正常'];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function apirule(){
        return $this->belongsTo('ApiRule','api_rule_id','id','','LEFT')->setEagerlyType(0);
    }


    /**
     * 获取列表数据
     */
    public static function getList(){

        $where = [
            'status'=>'1'
        ];

        $list = self::where($where)->order('weight','asc')->select();

        return collection($list)->toArray();
    }




}
