<?php

return array (
  0 => 
  array (
    'name' => 'appFlag',
    'title' => '应用标志',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'easypay',
    'rule' => '',
    'msg' => '',
    'tip' => '隔离应用，实现一户多用',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'account',
    'title' => '账户类型',
    'type' => 'radio',
    'content' => 
    array (
      'free' => '启用免费账户(需填 Subscribe key 和 Common key)',
      'pro' => '启用付费账户(需填 Client key、Rest key 和 Secret key)',
    ),
    'value' => 'free',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  2 => 
  array (
    'name' => 'subscribekey',
    'title' => 'Subscribe key',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'BS-1a51909cb335461d85d02651884de114',
    'rule' => '',
    'msg' => '',
    'tip' => '免费账户使用',
    'ok' => '',
    'extend' => '',
  ),
  3 => 
  array (
    'name' => 'commonkey',
    'title' => 'Common key',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'BC-ec051815f58948bbb2c19d9f210d95b9',
    'rule' => '',
    'msg' => '',
    'tip' => '免费账户使用',
    'ok' => '',
    'extend' => '',
  ),
  4 => 
  array (
    'name' => 'clientkey',
    'title' => 'Client key',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '',
    'rule' => '',
    'msg' => '',
    'tip' => '付费账户使用',
    'ok' => '',
    'extend' => '',
  ),
  5 => 
  array (
    'name' => 'restkey',
    'title' => 'Rest key',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '',
    'rule' => '',
    'msg' => '',
    'tip' => '付费账户使用',
    'ok' => '',
    'extend' => '',
  ),
  6 => 
  array (
    'name' => 'secretkey',
    'title' => 'Secret key',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '',
    'rule' => '',
    'msg' => '',
    'tip' => '付费账户使用',
    'ok' => '',
    'extend' => '',
  ),
  7 => 
  array (
    'name' => 'cdnhost',
    'title' => 'CDN Host',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'cdn-hangzhou.goeasy.io',
    'rule' => 'required',
    'msg' => '',
    'tip' => '中国区无需修改',
    'ok' => '',
    'extend' => '',
  ),
  8 => 
  array (
    'name' => 'resthost',
    'title' => 'REST Host',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'rest-hangzhou.goeasy.io',
    'rule' => 'required',
    'msg' => '',
    'tip' => '中国区无需修改',
    'ok' => '',
    'extend' => '',
  ),
  9 => 
  array (
    'name' => 'logger',
    'title' => '控制台打印提示',
    'type' => 'bool',
    'content' => 
    array (
    ),
    'value' => '1',
    'rule' => 'required',
    'msg' => '',
    'tip' => '开启后，在浏览器控制台打印连接状态日志',
    'ok' => '',
    'extend' => '',
  ),
  10 => 
  array (
    'name' => 'frontend',
    'title' => '启用前台默认的事件(index模块)',
    'type' => 'bool',
    'content' => 
    array (
    ),
    'value' => '1',
    'rule' => 'required',
    'msg' => '',
    'tip' => '前台可以直接监听top的GoeasyUserCommon和GoeasyUserMsg事件来处理消息',
    'ok' => '',
    'extend' => '',
  ),
  11 => 
  array (
    'name' => 'backend',
    'title' => '启用后台默认的事件(admin模块)',
    'type' => 'bool',
    'content' => 
    array (
    ),
    'value' => '1',
    'rule' => 'required',
    'msg' => '',
    'tip' => '后台可以直接监听top的GoeasyAdminCommon和GoeasyAdminMsg事件来处理消息',
    'ok' => '',
    'extend' => '',
  ),
);
