<?php

namespace app\admin\validate;

use think\Validate;

class ApiRule extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name|名称'=>'require|max:32|unique:api_rule,name',
        'api_type_id|支付类型'=>'require|integer',
        'api_account_ids|接口账户'=>'require',
        'type|调用方式'=>'require|in:0,1,2'
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
        'add'  => [],
        'edit' => [],
    ];
    
}
