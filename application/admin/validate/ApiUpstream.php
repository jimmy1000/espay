<?php

namespace app\admin\validate;

use think\Validate;

class ApiUpstream extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name|名称'=>'require|chsDash|max:20|unique:api_upstream,name',
        'code|编号'=>'require|alphaDash|max:24|unique:api_upstream,code',
        'params|参数'=>'array'
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['name','code','params'],
        'edit' => ['name','code','params'],
    ];
    
}
