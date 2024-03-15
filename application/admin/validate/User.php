<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'id'=>''
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
        //重设交易密钥
        'reset_md5_key'=>[
            'id'=>'require|number'
        ],
        //谷歌令牌解绑
        'reset_google_bind'=>[
            'id'=>'require|number'
        ],
        //手机号解绑
        'clear_mobile_bind'=>[
            'id'=>'require|number'
        ],
        //费率设置
        'apichannel'=>[
            'id'=>'require|number'
        ]
    ];
    
}
