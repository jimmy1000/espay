<?php

namespace app\index\controller;

use addons\goeasy\library\Goeasy;
use app\admin\model\ApiAccount;
use app\admin\model\ApiRule;
use app\admin\model\ApiType;
use app\common\controller\Frontend;
use app\common\library\Token;
use app\common\model\ApiChannel;
use app\common\model\Bank;
use app\common\model\Bankcard;
use app\common\model\Order;
use app\common\model\Pay;
use app\common\model\PayOrder;
use app\common\model\UserLog;
use Carbon\Carbon;
use fast\Date;
use fast\Http;
use fast\IdWork;
use fast\Random;
use fast\Rsa;
use think\Db;
use think\db\Builder;
use think\Log;
use think\Queue;
use think\Validate;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        $this->assign('title', '首页');
        return $this->view->fetch();
    }



    public function test()
    {


        $result = \app\common\model\ApiRule::getChannelInfo(4,true);

        var_dump($result);


    }

}
