<?php

namespace app\admin\validate;

use think\Validate;

class ApiAccount extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name|名称'=>'require|chsDash|max:20|unique:api_account,name',
        'ifrepay|是否代付'=>'require|in:0,1',
        'ifrecharge|是否内充通道'=>'require|in:0,1',
        'domain|接口域名'=>['regex'=>'/^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?\'\\+&%$#=~_-]+))*$/']
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
