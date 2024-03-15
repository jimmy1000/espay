<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [
    //'connector' => 'Sync',

    //redis
    'connector'=>'redis',
    'expire'     => 0,      //
    'default'    => 'default',
    'host'       => \think\Env::get('redis.host','127.0.0.1'),
    'port'       =>  \think\Env::get('redis.port','6379'),
    'password'   => \think\Env::get('redis.password','6379'),
    'select'     => 0,
    'timeout'    => 0,
    'persistent' => false

    //database
//    'connector' => 'database',
//    'expire'  => 60,
//    'default' => 'default',
//    'table'   => 'faqueue_jobs',
//    'dsn'     => [
//        'type' => 'mysql',
//        'database' => '',
//        'hostname' => '',
//        'username' => '',
//        'password' => '',
//        'prefix' => '',
//    ]

];