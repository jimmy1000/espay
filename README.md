天涯四方pay是一款基于Fastadmin的聚合支付系统，主要使用技术栈：THINPHP5 + VUE + Require.js + Redis + Mysql


## **主要特性**

* 基于`Auth`验证的权限管理系统
    * 支持无限级父子级权限继承，父级的管理员可任意增删改子级管理员及权限设置
    * 支持单管理员多角色
    * 强大的日志功能
* 安全性
    * 谷歌验证码和短信验证码强制开启
    * 银行卡审核、会员认证
    * 多级过滤机制   
* 完全前后端分离
    * 采用VUE ELEMENTUI 前后端完全分离
* 强大的聚合支付功能
    * 代理分润
    * 多通道轮询
    * 接口检测
    * API代付
