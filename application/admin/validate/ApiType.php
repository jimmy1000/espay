<?php

namespace app\admin\validate;

use think\Validate;

class ApiType extends Validate
{


    /**
     * 验证规则
     */
    protected $rule = [
        'name|名称'=>'require|chsDash|max:20|unique:api_type,name',
        'code|调用编号'=>'require|alphaDash|max:24|unique:api_type,code',
        'status|状态'=>'require|in:0,1',
        'default|默认开关'=>'require|in:0,1',
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
        'add'=>['name','code','status','default','domain'],
        'edit'=>['name','code','status','default','domain']
    ];

}
