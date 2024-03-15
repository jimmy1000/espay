<?php

namespace app\admin\controller\api;

use app\admin\model\ApiAccount;
use app\admin\model\ApiType;
use app\admin\model\ApiUpstream;
use app\common\controller\Backend;
use app\common\model\ApiChannel;
use app\common\model\ApiLog;
use app\common\model\Order;
use fast\Tree;
use think\Db;

/**
 * 接口账户
 *
 * @icon fa fa-circle-o
 */
class Account extends Backend
{

    /**
     * ApiAccount模型对象
     * @var \app\admin\model\ApiAccount
     */
    protected $model = null;

    protected $modelValidate = true;

    protected $noNeedRight = ['repay'];

    protected $relationSearch = true;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ApiAccount;
        $this->view->assign("ifrepayList", $this->model->getIfrepayList());
        $this->view->assign("ifrechargeList", $this->model->getIfrechargeList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['upstream'])
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with(['upstream'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }
    
    
    /**
     * 接口检测
     */
    public function check()
    {
        //如果是ajax则请求接口文件
        if ($this->request->isAjax()) {

            $data = $this->request->only(['id', 'channel', 'amount', 'params']);
            $rules = [
                'id' => 'require|number',
                'channel|调用编号' => 'require|alphaDash|max:24',
                'amount|金额' => 'require|float|>=:10',
            ];

            $result = $this->validate($data, $rules);

            if (true !== $result) {
                $this->error($result);
            }

            $accountModel = ApiAccount::get($data['id']);
            $apiTypeModel = ApiType::get(['code' => $data['channel']]);
            $upstreamModel = $accountModel->upstream;
            $api_upstream_code = $upstreamModel->code;

            $params = [];
            if (!empty($data['params'])) {
                parse_str($data['params'], $params);
            }

            $domain = config('site.gateway');   //返回的域名
            //获取域名
            if ($accountModel['domain']) {
                $domain = $accountModel['domain'];
            } elseif (!empty($apiTypeModel['domain'])) {
                $domain = $apiTypeModel['domain'];
            }
            //提交给接口的参数
            $params = [
                'config' => $accountModel['params'], //配置参数
                'merId' => 'check',                //商户号
                'sys_orderno' => time(),    //订单号
                'total_money' => $data['amount'],       //订单金额
                'channel' => $data['channel'],            //通道代码
                'desc' => empty($desc) ? '' : $desc,                  //简单描述
                'bankcode' => empty($params['bankcode']) ? '' : $params['bankcode'],                                //银行代码
                'user_id' => empty($params['userId']) ? '' : $params['userId'],           //快捷模式必须
                'ip' => '127.0.0.1',                                          //ip地址
                'domain' => $domain,                                 //地址信息
                'notify_url' => $domain . '/Pay/notify/code/' . $api_upstream_code,
                'return_url' => $domain . '/Pay/return/code/' . $api_upstream_code,
            ];
            $api = loadApi($api_upstream_code);
            $result = $api->pay($params);
            if ($result[0] == 0) {
                $msg = $result[1];
                $this->error($msg, null);
            }
            $this->success('获取成功', null, [
                'result' => $result[1]
            ]);
        }

        return $this->fetch();
    }

    /**
     * 费率以及通道设置
     */
    public function channel()
    {

        $data = $this->request->only('id');

        //获取接口账户
        $row = $this->model->hidden(['params'])->find($data['id']);

        if (!$row) {
            $this->error('记录不存在！');
        }

        //获取接口类型
        $api_type_list = ApiType::getOpenList();
        $api_type_ids = array_column($api_type_list, 'id');

        if ($this->request->isPost()) {
            Db::startTrans();
            try {
                //1、先获取到没有勾选的接口类型，从中间表删除掉这些
                //2、遍历选中的，添加或者修改数据
                $row = $this->request->param('row/a', []);
                $selected_type_list = $row['types'];
                //删除未勾选的记录
                $noselected_type_list = array_diff($api_type_ids, $selected_type_list);
                if (count($noselected_type_list) > 0) {
                    ApiChannel::destroy(function ($query) use ($data, $noselected_type_list) {
                        $query->where('api_account_id', $data['id']);
                        $query->where('api_type_id', 'IN', implode(',', $noselected_type_list));
                    });
                }
                //插入数组,更新数组
                $inser_data = [];
                $update_data = [];
                foreach ($selected_type_list as $type_id) {
                    $param = $row[$type_id];
                    $model = ApiChannel::get(function ($query) use ($type_id, $data) {
                        $query->where([
                            'api_type_id' => $type_id,
                            'api_account_id' => $data['id']
                        ]);
                    });
                    if (is_null($model)) {
                        array_push($inser_data, [
                            'api_type_id' => $type_id,
                            'api_account_id' => $data['id'],
                            'upstream_rate' => $param['upstream_rate'],
                            'rate' => $param['rate'],
                            'minmoney' => $param['minmoney'],
                            'maxmoney' => $param['maxmoney'],
                            'daymoney' => $param['daymoney'],
                            'ifjump' => $param['ifjump'],
                            'status' => $param['status']
                        ]);
                    } else {
                        array_push($update_data, [
                            'id' => $model->id,
                            'api_type_id' => $type_id,
                            'api_account_id' => $data['id'],
                            'upstream_rate' => $param['upstream_rate'],
                            'rate' => $param['rate'],
                            'minmoney' => $param['minmoney'],
                            'maxmoney' => $param['maxmoney'],
                            'daymoney' => $param['daymoney'],
                            'ifjump' => $param['ifjump'],
                            'status' => $param['status']
                        ]);
                    }
                }

                //处理数据
                $model = new ApiChannel();
                $model->insertAll($inser_data);
                $model->isUpdate()->saveAll($update_data);
                Db::commit();

            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('设置成功！');
        }

        $this->assign('channel_list', ApiChannel::getChannelByAccount($data['id']));
        $this->assign('row', $row);
        $this->assign('api_type_list', $api_type_list);
        return $this->view->fetch();
    }


    public function items()
    {
        $list = collection(ApiAccount::all())->toArray();

        return json($list);
    }


    /**
     * 统计
     */
    public function statistics()
    {
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $result = [];


            $list = Order::where($where)->field('api_account_id,sum(total_money) as `total_money`,count(id) as `count`,COUNT(DISTINCT(merchant_id)) as `merchant_id`, status')->group('api_account_id,status')->select();

            if (count($list) <= 0) {
                $result = [];
                $total = 0;
            } else {

                foreach ($list as $i => $iList) {


                    $apiAccountModel = ApiAccount::get($iList['api_account_id']);

                    if (is_null($apiAccountModel)) {
                        continue;
                    }

                    //接口名称 通道名称 总交易金额 成功交易金额 发起笔数 成功笔数 支付人数 转化率 扣量金额 扣量笔 转化率（扣） 扣率（按笔） 扣率（金额）
                    $tmpallmoney = 0;
                    $tmpsuccessmoney = 0;
                    $tmpkoumoney = 0;
                    $tmpallnum = 0;
                    $tmpsuccessnum = 0;
                    $tmpkounum = 0;
                    $tmpusernum = 0;

                    $tmpallmoney += $iList['total_money'];
                    $tmpallnum += $iList['count'];

                    switch ($iList['status']) {
                        case '0':
                            break;
                        case '1':
                            $tmpsuccessmoney += $iList['total_money'];
                            $tmpsuccessnum += $iList['count'];
                            $tmpusernum += $iList['merchant_id'];
                            break;
                        case 2:
                            $tmpsuccessmoney += $iList['total_money'];
                            $tmpsuccessnum += $iList['count'];
                            $tmpkoumoney += $iList['total_money'];
                            $tmpkounum += $iList['count'];
                            $tmpusernum += $iList['merchant_id'];
                            break;
                    }
                    $thisid = $iList['api_account_id'];

                    //如果没有则初始化
                    if (empty($allArray[$thisid])) {
                        $allArray[$thisid] = array(
                            'id' => $apiAccountModel['id'],
                            'name' => $apiAccountModel['name'],
                            'allmoney' => 0,
                            'successmoney' => 0,
                            'koumoney' => 0,
                            'allnum' => 0,
                            'successnum' => 0,
                            'kounum' => 0,
                            'usernum' => 0,
                        );
                    }
                    //有了就累计
                    $allArray[$thisid]['allmoney'] += $tmpallmoney;
                    $allArray[$thisid]['successmoney'] += $tmpsuccessmoney;
                    $allArray[$thisid]['koumoney'] += $tmpkoumoney;
                    $allArray[$thisid]['allnum'] += $tmpallnum;
                    $allArray[$thisid]['successnum'] += $tmpsuccessnum;
                    $allArray[$thisid]['kounum'] += $tmpkounum;
                    $allArray[$thisid]['usernum'] += $tmpusernum;
                }

                //计算概率
                $result = array();
                foreach ($allArray as $i => $iAllArray) {
                    //时间 总和交易金额 成功交易金额 发起笔数 成功笔数  扣量金额 扣量笔 转化率 转化率（扣100%） 扣率（按笔0%） 扣率（金额1%）
                    if ($iAllArray['allnum'] == 0) {
                        $alllv = '-';
                        $koulv = '-';
                    } else {
                        $alllv = number_format($iAllArray['successnum'] / $iAllArray['allnum'] * 100, 2, '.', '') . '%';
                        $koulv = number_format($iAllArray['kounum'] / $iAllArray['allnum'] * 100, 2, '.', '') . '%';
                    }
                    if ($iAllArray['successnum'] == 0) {
                        $kounumlv = '-';
                        $koumoneylv = '-';
                    } else {
                        $kounumlv = number_format($iAllArray['kounum'] / $iAllArray['successnum'] * 100, 2, '.', '') . '%';
                        $koumoneylv = number_format($iAllArray['koumoney'] / $iAllArray['successmoney'] * 100, 2, '.', '') . '%';
                    }

                    $result[] = array(
                        'id' => $i,
                        'name' => $iAllArray['name'],
                        'allmoney' => $iAllArray['allmoney'],
                        'successmoney' => $iAllArray['successmoney'],
                        'koumoney' => $iAllArray['koumoney'],
                        'allnum' => $iAllArray['allnum'],
                        'successnum' => $iAllArray['successnum'],
                        'kounum' => $iAllArray['kounum'],
                        'alllv' => $alllv,
                        'koulv' => $koulv,
                        'kounumlv' => $kounumlv,
                        'koumoneylv' => $koumoneylv,
                        'usernum' => $iAllArray['usernum'],
                    );
                }
                $total = count($result);
            }

            $result = array("total" => $total, "rows" => $result);

            return json($result);
        }


        return $this->fetch();
    }


    /**
     * 代付账户列表
     */
    public function repay()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array)$this->request->request("q_word/a");
        //当前页
        $page = $this->request->request("pageNumber");
        //分页大小
        $pagesize = $this->request->request("pageSize");
        //搜索条件
        $andor = $this->request->request("andOr", "and", "strtoupper");
        //排序方式
        $orderby = (array)$this->request->request("orderBy/a");
        //显示的字段
        $field = $this->request->request("showField");
        //主键
        $primarykey = $this->request->request("keyField");
        //主键值
        $primaryvalue = $this->request->request("keyValue");
        //搜索字段
        $searchfield = (array)$this->request->request("searchField/a");
        //自定义搜索条件
        $custom = (array)$this->request->request("custom/a");
        //是否返回树形结构
        $istree = $this->request->request("isTree", 0);
        $ishtml = $this->request->request("isHtml", 0);
        if ($istree) {
            $word = [];
            $pagesize = 99999;
        }
        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
                $logic = $andor == 'AND' ? '&' : '|';
                $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
                foreach ($word as $k => $v) {
                    $query->where(str_replace(',', $logic, $searchfield), "like", "%{$v}%");
                }
                if ($custom && is_array($custom)) {
                    foreach ($custom as $k => $v) {
                        if (is_array($v) && 2 == count($v)) {
                            $query->where($k, trim($v[0]), $v[1]);
                        } else {
                            $query->where($k, '=', $v);
                        }
                    }
                }
            };
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = [];
        $total = $this->model->where($where)->where('ifrepay','1')->count();
        if ($total > 0) {
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $datalist = $this->model->where($where)
                ->where('ifrepay','1')
                ->order($order)
                ->page($page, $pagesize)
                ->field($this->selectpageFields)
                ->select();
            foreach ($datalist as $index => $item) {
                unset($item['password'], $item['salt']);
                $list[] = [
                    $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
                    $field      => isset($item[$field]) ? $item[$field] : '',
                    'pid'       => isset($item['pid']) ? $item['pid'] : 0
                ];
            }
            if ($istree) {
                $tree = Tree::instance();
                $tree->init(collection($list)->toArray(), 'pid');
                $list = $tree->getTreeList($tree->getTreeArray(0), $field);
                if (!$ishtml) {
                    foreach ($list as &$item) {
                        $item = str_replace('&nbsp;', ' ', $item);
                    }
                    unset($item);
                }
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => $list, 'total' => $total]);
    }
}
