<?php

namespace app\admin\model;

use think\Model;


class Pay extends Model
{


    // 表名
    protected $name = 'pay';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'style_text',
        'status_text',
        'daifustatus_text',
        'notify_status_text'
    ];
    

    
    public function getStyleList()
    {
        return ['0' => __('Style 0'), '1' => __('Style 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }

    public function getDaifustatusList()
    {
        return ['0' => __('Daifustatus 0'), '1' => __('Daifustatus 1'), '2' => __('Daifustatus 2'), '3' => __('Daifustatus 3')];
    }

    public function getNotifyStatusList()
    {
        return ['0' => __('Notify_status 0'), '1' => __('Notify_status 1'), '2' => __('Notify_status 2')];
    }


    public function getStyleTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['style']) ? $data['style'] : '');
        $list = $this->getStyleList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDaifustatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['daifustatus']) ? $data['daifustatus'] : '');
        $list = $this->getDaifustatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getNotifyStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['notify_status']) ? $data['notify_status'] : '');
        $list = $this->getNotifyStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
