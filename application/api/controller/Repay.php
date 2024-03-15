<?php
/**
 * Repay.php
 * 易聚合支付系统
 * =========================================================

 * ----------------------------------------------
 *
 *
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-11
 */

namespace app\api\controller;


use addons\goeasy\library\Goeasy;
use app\common\controller\Api;
use app\common\model\ApiAccount;
use app\common\model\ApiChannel;
use app\common\model\ApiLog;
use app\common\model\ApiRule;
use app\common\model\ApiType;
use app\common\model\Bank;
use app\common\model\Order;
use app\common\model\User;
use app\common\model\UserLog;
use Carbon\Carbon;
use easypay\Notify;
use fast\Random;
use think\Cache;
use think\Db;
use think\Log;

class Repay extends Api
{


    protected $noNeedLogin = [];        //不需要登录的方法
    protected $noNeedRight = '*';


    /**
     * 引入后台控制器的traits
     */
    use \app\api\library\traits\Api;

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {


        $data = $this->request->only(['orderno', 'status', 'date']);


        $rules = [
            'orderno|订单号' => 'alphaDash',
            'status|订单状态' => 'in:0,1,2,3',
            'date|日期范围' => 'array'
        ];
        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        $where = [
            'merchant_id' => $this->auth->merchant_id
        ];

        //筛选订单号
        if (!empty($data['orderno'])) {
            $where['orderno'] = ['like', '%' . $data['orderno'] . '%'];
        }
        //订单状态
        if (isset($data['status']) && $data['status'] != '') {
            $where['status'] = $data['status'];
        }

        //时间
        if (isset($data['date']) && is_array($data['date'])) {
            $data['date'][0] = $data['date'][0] / 1000;
            $data['date'][1] = $data['date'][1] / 1000;
            $where['createtime'] = ['between time', $data['date']];
        }


        //排序字段
        $orderField = $this->request->param('orderField', 'id');
        $sort = 'DESC';
        //分页字段
        $page = $this->request->param('page/d', 1);
        $pageLimit = 10;    //每页显示10条数据
        $offset = ($page - 1) * $pageLimit;

        //数据总数
        $total = \app\common\model\Pay::where($where)->count();

        $list = \app\common\model\Pay::where($where)
            ->order($orderField, $sort)
            ->limit($offset, $pageLimit)
            ->select();

        foreach ($list as $k => $v) {
            $v->visible(['orderno', 'style_text', 'money', 'name', 'ka', 'bank', 'province', 'city', 'zhihang', 'status', 'status_text', 'charge', 'createtime_text']);
        }

        $list = collection($list)->toArray();

        //统计数据
        $extend = [];

        //今日提现
        $extend['todayMoney'] = \app\common\model\Pay::whereTime('createtime', 'today')->where('merchant_id', $this->auth->merchant_id)->sum('money');
        $extend['todayPoundage'] = \app\common\model\Pay::whereTime('createtime', 'today')->where('merchant_id', $this->auth->merchant_id)->sum('charge');

        //昨日提现
        $extend['yesterMoney'] = \app\common\model\Pay::whereTime('createtime', 'yesterday')->where('merchant_id', $this->auth->merchant_id)->sum('money');
        $extend['yesterPoundage'] = \app\common\model\Pay::whereTime('createtime', 'yesterday')->where('merchant_id', $this->auth->merchant_id)->sum('charge');

        //总的提现
        $extend['allMoney'] = \app\common\model\Pay::where('merchant_id', $this->auth->merchant_id)->sum('money');
        $extend['allPoundage'] = \app\common\model\Pay::where('merchant_id', $this->auth->merchant_id)->sum('charge');


        //列表金额
        $extend['all'] = \app\common\model\Pay::where($where)->sum('money');
        $extend['poundage'] = \app\common\model\Pay::where($where)->sum('charge');


        $this->success('获取数据成功！', [
            'total' => $total,
            'list' => $list,
            'limit' => $pageLimit,
            'extend' => $extend
        ]);

    }

    /**
     * 代付记录导出
     */
    public function export()
    {

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $data = $this->request->only(['orderno', 'status', 'api_type_id', 'date']);

        $rules = [
            'orderno|订单号' => 'alphaDash',
            'status|订单状态' => 'in:0,1',
            'api_type_id|订单类型' => 'integer',
            'date|日期范围' => 'array'
        ];

        $data = $this->request->only(['orderno', 'status', 'date']);

        $rules = [
            'orderno|订单号' => 'alphaDash',
            'status|订单状态' => 'in:0,1,2,3',
            'date|日期范围' => 'array'
        ];
        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }
        $where = [
            'merchant_id' => $this->auth->merchant_id
        ];

        //筛选订单号
        if (!empty($data['orderno'])) {
            $where['orderno'] = ['like', '%' . $data['orderno'] . '%'];
        }
        //订单状态
        if (isset($data['status']) && $data['status'] != '') {
            $where['status'] = $data['status'];
        }

        //时间
        if (isset($data['date']) && is_array($data['date'])) {
            $data['date'][0] = $data['date'][0] / 1000;
            $data['date'][1] = $data['date'][1] / 1000;
            $where['createtime'] = ['between time', $data['date']];
        }


        //排序字段
        $orderField = $this->request->param('orderField', 'id');
        $sort = 'DESC';

        $list = \app\common\model\Pay::where($where)
            ->order($orderField, $sort)->select();


        foreach ($list as $k => $v) {
            $v->visible(['orderno', 'style_text', 'money', 'name', 'ka', 'bank', 'zhihang', 'status_text', 'charge', 'createtime_text']);
        }

        $list = collection($list)->toArray();


        if(empty($list)){
            $this->error('记录不存在!');
        }

        $excel = new \PHPExcel();
        $excel->getProperties()
            ->setCreator("EasyPay")
            ->setLastModifiedBy("EasyPay")
            ->setTitle($this->auth->merchant_id . "->代付订单导出")
            ->setSubject($this->auth->merchant_id . "->代付订单导出导出");
        $excel->getDefaultStyle()->getFont()->setName('Microsoft Yahei');
        $excel->getDefaultStyle()->getFont()->setSize(12);

        $this->sharedStyle = new \PHPExcel_Style();
        $this->sharedStyle->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '000000')
                ),
                'font' => array(
                    'color' => array('rgb' => "000000"),
                ),
                'alignment' => array(
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'indent' => 1
                ),
                'borders' => array(
                    'allborders' => array('style' => \PHPExcel_Style_Border::BORDER_THIN),
                )
            ));

        $worksheet = $excel->setActiveSheetIndex(0);
        $worksheet->setTitle($this->auth->merchant_id . "->代付订单导出导出");
        $line = 1;


        $styleArray = array(
            'font' => array(
                'bold' => false,//加粗
                'color' => array('rgb' => '000000'),//字体颜色
                'size' => 10,//字体大小
                'name' => 'Verdana'
            ));

        $fieldArray = [
            'orderno' => '订单号',
            'style_text' => '订单类型',
            'money' => '金额',
            'charge' => '手续费',
            'name' => '账户',
            'ka' => '卡号',
            'bank' => '银行',
            'zhihang' => '支行',
            'status_text' => '状态',
            'createtime_text' => '申请时间'
        ];

        $items = $list;
        foreach ($items as $index => $item) {
            $line++;
            $col = 0;
            foreach ($item as $field => $value) {

                if (empty($fieldArray[$field])) {
                    continue;
                }
                if ($field == 'orderno' || $field == 'ka') {
                    $value = ' ' . $value;
                }

                $worksheet->setCellValueByColumnAndRow($col, $line, $value);
                $worksheet->getStyleByColumnAndRow($col, $line)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $worksheet->getCellByColumnAndRow($col, $line)->getStyle()->applyFromArray($styleArray);
                $col++;
            }
        }
        $first = array_keys($items[0]);
        foreach ($first as $index => $item) {
            if (empty($fieldArray[$item])) {
                continue;
            }
            $worksheet->setCellValueByColumnAndRow($index, 1, $fieldArray[$item]);
        }
        $excel->createSheet();
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        UserLog::addLog($this->auth->merchant_id, '导出代付订单记录');
        $objWriter->save('php://output');
        exit('');

    }


    /**
     * 批量代付
     *
     */

    public function batchapply()
    {

        $data = $this->request->only(['payPassword', 'codeStyle', 'smsCode', 'googleCode', 'list']);
        $rules = [
            'list|提现列表' => 'require|array|length:1,20',
            'payPassword|支付密码' => 'require|length:6,16',
            'codeStyle|验证码类型' => 'require|in:1,2',
            'smsCode|短信验证码' => 'requireIf:codeStyle,1',
            'googleCode|谷歌验证码' => 'requireIf:codeStyle,2'
        ];

        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        $userModel = $this->auth->getUser();

        //商户号
        $merchant_id = $userModel['merchant_id'];


        //用户余额
        $balance = $userModel['money'];
        //冻结金额
        $freezeMoney = $userModel->getFreezeMoney();
        //可用金额
        $userMoney = bcsub($balance, $freezeMoney, 2);

        if ($userModel['batchrepay'] == '0') {
            $this->error('您当前不支持批量代付，如有需要请联系商务开通。');
        }

        //判断是否在代付时间内
        if (!checkRepayTime()) {
            $this->error('请在提现允许时间段内操作！');
        }


        $dataList = [];     //结算信息
        $totalMoney = 0; //总金额
        $tixianMoney = 0; //支付金额
        $shouxuMoney = 0; //手续金额

        $counter = 0;
        foreach ($data['list'] as $item) {

            $commission = 0;
            $money = 0;
            ++$counter;

            $money = $item['代付金额'];
            if (!is_numeric($money) || $money <= 0) {
                $this->error('金额格式有误，请确保金额为数字。');
            }

            if (empty($item['开户名']) || !\think\Validate::is($item['开户名'], 'chsAlpha')) {
                $this->error('开户名格式有误，只能为汉字或者字母。');
            }

            if (empty($item['银行名称']) || !\think\Validate::is($item['银行名称'], 'chsAlpha')) {
                $this->error('银行名格式有误，只能为汉字或者字母。');
            }


            if (empty($item['支行名称']) || !\think\Validate::is($item['支行名称'], 'chsDash')) {
                $this->error('支行格式有误，只能为汉字，字母，数字。');
            }


            if (empty($item['银行帐号']) || !\think\Validate::is($item['银行帐号'], 'number')) {
                $this->error('银行账户格式错误');
            }


            $commission = $userModel->commission($money);
            $needMoney = bcadd($money, $commission, 2);
            $tixianMoney = bcadd($tixianMoney, $money, 2);
            $totalMoney = bcadd($totalMoney, $needMoney, 2);
            $shouxuMoney = bcadd($shouxuMoney, $commission, 2);
            $dataList[] = [
                'merchant_id' => $merchant_id,
                'orderno' => $userModel->id.Random::getOrderId() . $counter,
                'style' => '0',
                'money' => $money,
                'name' => $item['开户名'],
                'ka' => $item['银行帐号'],
                'bank' => $item['银行名称'],
                'province' => '',
                'city' => '',
                'zhihang' => $item['支行名称'],
                'status' => '0',
                'daifustatus' => '0',
                'charge' => $commission,
                'req_info' => '',
                'req_ip' => $this->request->ip(),
                'createtime' => time()
            ];
        }

        //判断结算金额是否超出
        if ($totalMoney > $userMoney) {
            $this->error('支付金额不足,当前需要' . $totalMoney . '元,手续费：' . $shouxuMoney . '元！');
        }

        //检查最小提现金额
        if ($tixianMoney < config('site.minpay')) {
            $this->error('支付金额小于最小要求金额！最低支付' . config('site.minpay') . '元');
        }


        //检查验证码
        $code = $data['codeStyle'] == 1 ? $data['smsCode'] : $data['googleCode'];
        $this->checkUserCode($data['codeStyle'], $code, 'batchrepay');

        //验证支付密码是否正确
        $flag = \app\common\model\User::verifyPayPassword($data['payPassword'], $this->auth->id);

        if (!$flag) {
            $this->error('支付密码输入不正确!');
        }


        $redislock = redisLocker();
        $resource = $redislock->lock('pay.' . $merchant_id, 3000);   //单位毫秒


        if ($resource) {
            //开始事务
            Db::startTrans();

            try {
                $payModel = new \app\common\model\Pay();
                $payModel->saveAll($dataList);
                //更新用户金额
                $userModel->setInc('withdrawal', $tixianMoney);
                //资金变动
                User::money(-$totalMoney, $userModel->id, '提现：' . $tixianMoney . '元，手续费：' . $shouxuMoney . '元', '代付批量提交', '2');
                //写入用户日志表
                UserLog::addLog($this->auth->merchant_id, '批量提现：' . $tixianMoney . '元，手续费：' . $shouxuMoney . '元');
                Db::commit();
                $redislock->unlock(['resource' => 'pay.' . $merchant_id, 'token' => $resource['token']]);

            } catch (\Exception $e) {
                Db::rollback();
                $redislock->unlock(['resource' => 'pay.' . $merchant_id, 'token' => $resource['token']]);
                $this->error($e->getMessage());
            }

        } else {
            $this->error('系统繁忙,请重新提交');
        }


        Notify::repay();


        //判断自动代付提交
        if ($userModel['ifdaifuauto'] == '1') {
            if ($userModel['daifuid'] > 0) {
                try {
                    foreach ($dataList as $item) {
                        \app\common\model\Pay::dfSubmit($item['orderno'], $userModel['daifuid'], true);
                    }
                } catch (\Exception $e) {
                    Log::record('自动代付异常,商户号:' . $this->auth->merchant_id . '异常信息:' . $e->getMessage(), 'REPAY_ERROR');
                }
            }
        }
        $this->success('申请成功。');
    }

    /**
     * 申请代付
     */
    public function apply()
    {

        $data = $this->request->only(['money', 'bankcardId', 'payPassword', 'codeStyle', 'smsCode', 'googleCode']);

        $rules = [
            'money|提现金额' => 'require|float|>:0',
            'bankcardId|提款银行卡' => 'require|integer',
            'payPassword|支付密码' => 'require|length:6,16',
            'codeStyle|验证码类型' => 'require|in:1,2',
            'smsCode|短信验证码' => 'requireIf:codeStyle,1',
            'googleCode|谷歌验证码' => 'requireIf:codeStyle,2'
        ];

        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        $userModel = $this->auth->getUser();
        //商户号
        $merchant_id = $userModel['merchant_id'];
        //提多少钱
        $money = $data['money'];
        //用户余额
        $balance = $userModel['money'];
        //冻结金额
        $freezeMoney = $userModel->getFreezeMoney();
        //可用金额
        $userMoney = bcsub($balance, $freezeMoney, 2);
        //手续费
        $commission = $userModel->commission($money);
        $needMoney = bcadd($money, $commission, 2);

        //判断是否在代付时间内
        if (!checkRepayTime()) {
            $this->error('请在提现允许时间段内操作！');
        }

        //判断余额是否足够
        if (!is_numeric($money) || $money <= 0) {
            $this->error('请填写正确的金额');
        }

        if (floatval($needMoney) > floatval($userMoney)) {
            $this->error('支付金额不足,需要' . $needMoney . '元,手续费：' . $commission . '元，可用余额：'.$userMoney.'元');
        }

        // 检查银行卡是否存在
        $bankcardModel = \app\common\model\Bankcard::where([
            'id' => $data['bankcardId'],
            'merchant_id' => $this->auth->merchant_id,
            'status' => '1'
        ])->find();

        if (is_null($bankcardModel)) {
            $this->error('银行卡无法支付，请更换');
        }

        //检查最小提现金额
        if ($money < config('site.minpay')) {
            $this->error('支付金额小于最小要求金额！最低支付' . config('site.minpay') . '元');
        }

        //检查验证码
        $code = $data['codeStyle'] == 1 ? $data['smsCode'] : $data['googleCode'];
        $this->checkUserCode($data['codeStyle'], $code, 'repay');


        //验证支付密码是否正确
        $flag = \app\common\model\User::verifyPayPassword($data['payPassword'], $this->auth->id);

        if (!$flag) {
            $this->error('支付密码输入不正确!');
        }

        //为了避免同时修改数据 还是加一下锁

        $redislock = redisLocker();
        $resource = $redislock->lock('pay.' . $merchant_id, 3000);   //单位毫秒

        if ($resource) {

            //开始事务
            Db::startTrans();

            try {
                $data = [
                    'merchant_id' => $merchant_id,
                    'orderno' => \app\common\model\Pay::createOrderNo(),
                    'style' => '0',
                    'money' => $money,
                    'name' => $bankcardModel['name'],
                    'ka' => $bankcardModel['ka'],
                    'bank' => $bankcardModel['bank'],
                    'province' => $bankcardModel['province'],
                    'city' => $bankcardModel['city'],
                    'zhihang' => $bankcardModel['zhihang'],
                    'status' => '0',
                    'daifustatus' => '0',
                    'charge' => $commission,
                    'req_info' => '',
                    'req_ip' => $this->request->ip(),
                    'createtime' => time()
                ];

                $payModel = \app\common\model\Pay::create($data);

                //更新用户金额
                $userModel->setInc('withdrawal', $money);
                //资金变动
                User::money(-$needMoney, $userModel->id, '提现：' . $money . '元，手续费：' . $commission . '元', $payModel['orderno'], '2');

                //写入用户日志表
                UserLog::addLog($this->auth->merchant_id, '申请提现：' . $money . '元，手续费：' . $commission . '元');

                Db::commit();
                $redislock->unlock(['resource' => 'pay.' . $merchant_id, 'token' => $resource['token']]);
            } catch (\Exception $e) {
                Db::rollback();
                $redislock->unlock(['resource' => 'pay.' . $merchant_id, 'token' => $resource['token']]);
                $this->error($e->getMessage());
            }

        } else {
            $this->error('系统繁忙,请重新提交');
        }


        Notify::repay();

        //判断自动代付提交
        if ($userModel['ifdaifuauto'] == '1') {
            if ($userModel['daifuid'] > 0) {
                try {
                    \app\common\model\Pay::dfSubmit($payModel->id, $userModel['daifuid']);
                } catch (\Exception $e) {
                    Log::record('自动代付异常,商户号:' . $this->auth->merchant_id . '异常信息:' . $e->getMessage(), 'REPAY_ERROR');
                }
            }
        }

        $this->success('申请成功。');

    }

}