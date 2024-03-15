<?php
/**
 * Order.php
 * 易聚合支付系统
 * =========================================================

 * ----------------------------------------------
 *
 *
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 本源码仅供技术学习研究使用,请勿用于非法用途,如产生法律纠纷与作者无关。
 * =========================================================
 * @author : 666666@qq.com
 * @date : 2019-05-06
 */

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\UserLog;
use think\Log;

class Order extends Api
{

    protected $noNeedRight = ['index','export'];



    public function _initialize()
    {
        parent::_initialize();
    }


    public function index()
    {
        $data = $this->request->only(['orderno', 'status', 'api_type_id', 'createtime','paytime']);

        $rules = [
            'orderno|订单号' => 'alphaDash',
            'status|订单状态' => 'in:0,1',
            'api_type_id|订单类型' => 'integer',
            'createtime|创建时间' => 'array',
            'paytime|支付时间'=>'array'
        ];

        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        //查询条件
        $where = [
            'order.merchant_id' => $this->auth->merchant_id
        ];
        $where2 = [
            'merchant_id'=>$this->auth->merchant_id
        ];

        //筛选订单号
        if (!empty($data['orderno'])) {
            $where['order.orderno'] = ['like', '%' . $data['orderno'] . '%'];
            $where2['orderno'] = ['like', '%' . $data['orderno'] . '%'];
        }
        //订单状态
        if (isset($data['status']) && $data['status'] != '') {
            $where['order.status'] = $data['status'];
        }
        //请求类型
        if (!empty($data['api_type_id'])) {
            $where['order.api_type_id'] = $data['api_type_id'];
            $where2['api_type_id'] = $data['api_type_id'];
        }

        //时间
        if (isset($data['createtime']) && is_array($data['createtime'])) {
            $data['createtime'][0] = $data['createtime'][0] / 1000;
            $data['createtime'][1] = $data['createtime'][1] / 1000;
            $where['order.createtime'] = ['between time', $data['createtime']];
            $where2['createtime'] = ['between time', $data['createtime']];

        }

        if (isset($data['paytime']) && is_array($data['paytime'])) {
            $data['paytime'][0] = $data['paytime'][0] / 1000;
            $data['paytime'][1] = $data['paytime'][1] / 1000;
            $where['order.paytime'] = ['between time', $data['paytime']];
            $where2['paytime'] = ['between time', $data['paytime']];

        }


        //排序字段
        $orderField = $this->request->param('orderField', 'id');
        $sort = 'DESC';
        //分页字段
        $page = $this->request->param('page/d', 1);
        $pageLimit = 10;    //每页显示10条数据
        $offset = ($page - 1) * $pageLimit;

        //数据总数
        $total = \app\common\model\Order::with(['apitype' => function ($query) {
            $query->withField('name');
        }])->where($where)->count();

        $list = \app\common\model\Order::
        with(['apitype' => function ($query) {
            $query->withField('name');
        }])
            ->where($where)
            ->order($orderField, $sort)
            ->limit($offset, $pageLimit)
            ->select();

        foreach ($list as $k => $v) {
            $v->visible(['orderno', 'sys_orderno', 'total_money', 'have_money', 'style_text', 'status', 'status_text', 'notify_status_text', 'createtime_text', 'paytime_text', 'apitype']);
        }
        $list = collection($list)->toArray();


        //统计信息
        $extend = [];

        //今日收益
        $extend['today'] = \app\common\model\Order::whereTime('createtime','today')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->sum('have_money');
        //昨日收益
        $extend['yesterday'] = \app\common\model\Order::whereTime('createtime','yesterday')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->sum('have_money');;

        //当前列表金额
        $extend['all'] = \app\common\model\Order::where($where2)->sum('total_money');

        //成功的金额
        $where2['status'] = '1';
        $extend['success'] = \app\common\model\Order::where($where2)->sum('total_money');

        //当日成功率

        $count = \app\common\model\Order::whereTime('createtime','today')->where('merchant_id',$this->auth->merchant_id)->count();

        $succCount = \app\common\model\Order::whereTime('createtime','today')->where('status','1')->where('merchant_id',$this->auth->merchant_id)->count();

        if($count == 0){
            $extend['successRate'] = 100;
        }else{
            $extend['successRate'] =  number_format($succCount / $count * 100,2);
        }

        //新增应结算的金额
        $extend['have'] = \app\common\model\Order::where($where2)->sum('have_money');

        $this->success('获取数据成功！', [
            'total' => $total,
            'list' => $list,
            'limit' => $pageLimit,
            'extend'=>$extend
        ]);

    }

    /**
     * 订单导出
     */
    public function export()
    {

        set_time_limit(0);


        $data = $this->request->only(['orderno', 'status', 'api_type_id', 'createtime','paytime']);

        $rules = [
            'orderno|订单号' => 'alphaDash',
            'status|订单状态' => 'in:0,1',
            'api_type_id|订单类型' => 'integer',
            'createtime|创建时间' => 'array',
            'paytime|支付时间'=>'array'
        ];



        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        //查询条件
        $where = [
            'order.merchant_id' => $this->auth->merchant_id
        ];
        //筛选订单号
        if (!empty($data['orderno'])) {
            $where['order.orderno'] = ['like', '%' . $data['orderno'] . '%'];
        }
        //订单状态
        if (isset($data['status']) && $data['status'] != '') {
            $where['order.status'] = $data['status'];
        }
        //请求类型
        if (!empty($data['api_type_id'])) {
            $where['order.api_type_id'] = $data['api_type_id'];
        }



        //时间
        if (isset($data['createtime']) && is_array($data['createtime'])) {
            $data['createtime'][0] = $data['createtime'][0] / 1000;
            $data['createtime'][1] = $data['createtime'][1] / 1000;
            $where['order.createtime'] = ['between time', $data['createtime']];

        }

        if (isset($data['paytime']) && is_array($data['paytime'])) {
            $data['paytime'][0] = $data['paytime'][0] / 1000;
            $data['paytime'][1] = $data['paytime'][1] / 1000;
            $where['order.paytime'] = ['between time', $data['paytime']];

        }

        //排序字段
        $orderField = $this->request->param('orderField', 'id');
        $sort = 'DESC';

        $excel = new \PHPExcel();
        $excel->getProperties()
            ->setCreator("EasyPay")
            ->setLastModifiedBy("EasyPay")
            ->setTitle($this->auth->merchant_id."->订单导出")
            ->setSubject($this->auth->merchant_id."->订单导出");
        $excel->getDefaultStyle()->getFont()->setName('Microsoft Yahei');
        $excel->getDefaultStyle()->getFont()->setSize(12);

        $this->sharedStyle = new \PHPExcel_Style();
        $this->sharedStyle->applyFromArray(
            array(
                'fill'      => array(
                    'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '000000')
                ),
                'font'      => array(
                    'color' => array('rgb' => "000000"),
                ),
                'alignment' => array(
                    'vertical'   => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'indent'     => 1
                ),
                'borders'   => array(
                    'allborders' => array('style' => \PHPExcel_Style_Border::BORDER_THIN),
                )
            ));

        $worksheet = $excel->setActiveSheetIndex(0);
        $worksheet->setTitle($this->auth->merchant_id."->订单导出");

        $line = 1;
        $list = [];

        $list = \app\common\model\Order::
        with(['apitype' => function ($query) {
            $query->withField('name');
        }])
            ->where($where)
            ->select();

        foreach ($list as $k => $v) {
            $v->visible(['orderno', 'sys_orderno', 'total_money', 'have_money', 'style_text',  'status_text', 'notify_status_text', 'createtime_text', 'paytime_text']);
        }
        $items = collection($list)->toArray();


        if(empty($items)){
            $this->error('记录不存在!');
        }


        $styleArray = array(
            'font' => array(
                'bold'  => false,//加粗
                'color' => array('rgb' => '000000'),//字体颜色
                'size'  => 10,//字体大小
                'name'  => 'Verdana'
            ));

        $fieldArray = [
            'orderno'=>'商户订单号',
            'sys_orderno'=>'系统订单号',
            'total_money'=>'订单金额',
            'have_money'=>'获得金额',
            'style_text'=>'订单类型',
            'status_text'=>'状态',
            'notify_status_text'=>'通知状态',
            'createtime_text'=>'添加时间',
            'paytime_text'=>'支付时间'
        ];

        foreach ($items as $index => $item) {
            $line++;
            $col = 0;
            foreach ($item as $field => $value) {

                if($field == 'orderno' || $field == 'sys_orderno'){
                    $value = ' '.$value;
                }
                $worksheet->setCellValueByColumnAndRow($col, $line, $value);
                $worksheet->getStyleByColumnAndRow($col, $line)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $worksheet->getCellByColumnAndRow($col, $line)->getStyle()->applyFromArray($styleArray);
                $col++;
            }
        }
        $first = array_keys($items[0]);
        foreach ($first as $index => $item) {
            $worksheet->setCellValueByColumnAndRow($index, 1, $fieldArray[$item]);
        }
        $excel->createSheet();
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        UserLog::addLog($this->auth->merchant_id, '导出订单记录');
        $objWriter->save('php://output');
        exit('');

    }
}