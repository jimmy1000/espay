<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
    'action_begin' => 
    array (
      0 => 'geetest',
    ),
    'config_init' => 
    array (
      0 => 'geetest',
      1 => 'goeasy',
      2 => 'nkeditor',
    ),
    'admin_login_init' => 
    array (
      0 => 'loginbg',
    ),
    'response_send' => 
    array (
      0 => 'loginvideo',
    ),
    'sms_send' => 
    array (
      0 => 'smsbao',
    ),
    'sms_notice' => 
    array (
      0 => 'smsbao',
    ),
    'sms_check' => 
    array (
      0 => 'smsbao',
    ),
  ),
  'route' => 
  array (
    '/example$' => 'example/index/index',
    '/example/d/[:name]' => 'example/demo/index',
    '/example/d1/[:name]' => 'example/demo/demo1',
    '/example/d2/[:name]' => 'example/demo/demo2',
    '/qrcode$' => 'qrcode/index/index',
    '/qrcode/build$' => 'qrcode/index/build',
    0 => 
    array (
      'addon' => 'mq',
      'domain' => 'http://easypay.com',
      'rule' => 
      array (
      ),
    ),
  ),
);