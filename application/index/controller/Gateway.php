<?php
/**
 * Gateway.php
 * 易聚合支付系统
 * =========================================================
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-08
 */

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Bank;
use app\common\model\Order;
use fast\Http;
use fast\Random;
use fast\Rsa;
use think\Config;
use think\Cookie;
use think\Hook;

class Gateway extends Frontend
{

    protected $noNeedLogin = ['*'];


    public function _initialize()
    {
        parent::_initialize();
    }


    public function index()
    {


        //开始获取数据
        $data = $this->request->only([
            'merId', 'orderId', 'orderAmt', 'channel', 'desc', 'attch', 'smstyle', 'userId', 'ip', 'notifyUrl', 'returnUrl', 'nonceStr', 'sign', 'bankcode', 'gateway'
        ]);

        //校验规则
        $rules = [
            'merId|商户号' => 'require|number',
            'orderId|订单号' => 'require|alphaDash|max:' . config('site.order_length'),
            'orderAmt|订单金额' => 'require|float',
            'channel|支付类型' => 'require|alphaDash|max:24',
            'desc|描述' => 'require|chsDash|max:64',
            'attch|附加信息' => 'alphaDash|max:64',
            'smtyle|扫码模式' => 'in:0,1',
            'userId|用户id' => 'requireIf:channel,ylkj',
            'ip|IP地址' => 'require|ip',
            'notifyUrl|异步地址' => 'require|url',
            'returnUrl|同步地址' => 'require|url',
            'nonceStr|随机字符串' => 'require|max:32',
            'sign|签名' => 'require',
            'bankcode|银行代码' => 'alphaNum|max:16',
            'gateway|收银台标识' => 'require|in:1'
        ];

        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        $list = Bank::getList();

        $api = \config('site.gateway').'/Pay';

        $this->assign('list',$list);
        $this->assign('api',$api);
        $this->assign('data',$data);

        return $this->fetch();
    }
}