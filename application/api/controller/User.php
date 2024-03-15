<?php

namespace app\api\controller;

use app\admin\model\UserAuth;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\library\Sms as Smslib;

use app\common\model\ApiAccount;
use app\common\model\ApiChannel;
use app\common\model\ApiLog;
use app\common\model\ApiRule;
use app\common\model\ApiType;
use app\common\model\Attachment;
use app\common\model\Bank;
use app\common\model\Order;
use app\common\model\SensitiveAttachment;
use app\common\model\UserLog;
use Carbon\Carbon;
use fast\Random;
use google\GoogleAuthenticator;
use think\Cache;
use think\Config;
use think\File;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{

    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third', 'exists'];

    //不需要权限检查的方法
    protected $noNeedRight = ['dashboard', 'info', 'setpaypassword', 'getgoogleqrcode', 'bindgoogleqrcode', 'cleargooglebind',
        'logout', 'authupload', 'auth', 'getauthinfo', 'attach', 'bindmobile', 'clearmobilebind', 'changePassword', 'logs',
        'recharge','moneylog'];


    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @param string $account 账号
     * @param string $password 密码
     */
    public function login()
    {

        $account = $this->request->param('account');
        $password = $this->request->param('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }

        $geetestValidate = Validate::is('ok', 'captcha');
        if (!$geetestValidate) {
            $this->error('验证码校验失败，请重新校验', '');
        }

        $ret = $this->auth->login($account, $password);
        if ($ret) {
            UserLog::addLog($this->auth->merchant_id, '用户登录');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     */
    public function register()
    {

        $username = $this->request->param('username', '');
        $password = $this->request->param('password', '');
        $email = $this->request->param('email', '');
        $mobile = $this->request->param('mobile', '');

        $extend = [];
        $extend['group_id'] = 1;
        //检测系统是否关闭注册
        if (config('site.reg_switch') == 0) {
            $this->error('系统关闭注册，开户请联系客服');
        }

        //是否允许代理注册
        if (config('site.agent_switch') == 1) {
            $extend['group_id'] = $this->request->param('agent') == 0 ? 1 : 2;
        }

        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }

        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        $geetestValidate = Validate::is('ok', 'captcha');
        if (!$geetestValidate) {
            $this->error('验证码校验失败，请重新校验', '');
        }

        $ret = $this->auth->register($username, $password, $email, $mobile, $extend);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        UserLog::addLog($this->auth->merchant_id, '用户登出');
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }


    /**
     * 用户是否存在
     */
    public function exists()
    {

        $field = $this->request->param('field');
        //判断是否有该记录
        $res = \app\common\model\User::where(['username' => $field])
            ->whereOr(['mobile' => $field])
            ->whereOr(['email' => $field])->find();
        $res = is_null($res) ? false : true;
        $this->success('', $res);
    }


    /**
     * 用户信息包前端权限列表
     */
    public function info()
    {


        $user_info = $this->auth->getUserinfo();
        $user_info['avatar'] = cdnurl($user_info['avatar'], true);

        //是否设置了提现密码
        $user_info['setPayPassword'] = empty($user_info['paypassword']) ? false : true;
        $roles = $user_info['group_id'] == 2 ? 'agency' : 'member'; //代理还是会员
        $user_info['settle'] = $this->auth->getUser()->settle();
        $user_info['freezeMoney'] = $this->auth->getUser()->getFreezeMoney();   //用户冻结的金额
        $user_info['availMoney'] = bcsub($user_info['money'],$user_info['freezeMoney'],'2');

        $data = [
            'userinfo' => $user_info,
            'roles' => [$roles]
        ];
        $this->success('', $data);
    }

    /**
     * 获取用户的认证信息
     */
    public function getAuthInfo()
    {

        $user_id = $this->auth->id;

        $authModel = \app\common\model\UserAuth::get([
            'user_id' => $user_id
        ]);

        if (is_null($authModel)) {
            $data = [
                'status' => -1,
                'status_text' => '未提交认证信息'
            ];
            return $this->success('您还有没有提交认证信息!', $data);
        }

        return $this->success('获取认证信息成功！', $authModel->toArray());

    }

    /**
     * 设置用户的支付密码
     */
    public function setPayPassword()
    {


        $rules = [
            'old|旧密码' => 'length:6,16',
            'password|支付密码' => 'require|length:6,16',
            'confirmPassword|确认密码' => 'require|confirm:password',
            'codeStyle|验证码类型' => 'require|in:1,2',
            'smsCode|短信验证码' => 'requireIf:codeStyle,1',
            'googleCode|谷歌验证码' => 'requireIf:codeStyle,2'
        ];

        $message = [
            'old.length' => '旧密码长度不符合要求，格式为：6-16位',
            'password.require' => '支付密码不能为空',
            'password.length' => '支付密码长度不符合要求，格式为：6-16位',
            'confirmPassword.require' => '确认支付密码不能为空',
            'confirmPassword.length' => '确认支付密码长度不符合要求，格式为：6-16位',
        ];

        $data = [
            'old' => $this->request->param('old', ''),
            'password' => $this->request->param('password', ''),
            'confirmPassword' => $this->request->param('confirmPassword', ''),
            'codeStyle' => $this->request->param('codeStyle', 1),
            'smsCode' => $this->request->param('smsCode', ''),
            'googleCode' => $this->request->param('googleCode', ''),
        ];

        $result = $this->validate($data, $rules, $message);
        if ($result !== true) {
            $this->error($result);
        }

        $code = $data['codeStyle'] == 1 ? $data['smsCode'] : $data['googleCode'];
        $this->checkUserCode($data['codeStyle'], $code, 'changepaypassword');


        $user_info = $this->auth->getUserinfo();
        $user_info['setPayPassword'] = empty($user_info['paypassword']) ? false : true;

        //如果没有设置支付密码，则设置
        if (!$user_info['setPayPassword']) {
            \app\common\model\User::setPayPassword($data['password'], $this->auth->id);
            UserLog::addLog($this->auth->merchant_id, '初始化设置支付密码');
            $this->success('设置支付密码成功，请妥善保管！');
        }

        //如果有设置先看看原支付密码是否正确
        if (empty($data['old'])) {
            $this->error('旧支付密码不能为空');
        }

        //检测密码是否正确
        if (!\app\common\model\User::verifyPayPassword($data['old'], $this->auth->id)) {
            $this->error('旧支付密码不正确');
        }

        \app\common\model\User::setPayPassword($data['password'], $this->auth->id);

        UserLog::addLog($this->auth->merchant_id, '修改支付密码');

        $this->success('设置支付密码成功，请妥善保管！');

    }

    /**
     * 重设登录密码
     */
    public function changePassword()
    {

        $rules = [
            'old|旧密码' => 'require|length:6,16',
            'password|支付密码' => 'require|length:6,16',
            'confirmPassword|确认密码' => 'require|confirm:password',
            'codeStyle|验证码类型' => 'require|in:1,2',
            'smsCode|短信验证码' => 'requireIf:codeStyle,1',
            'googleCode|谷歌验证码' => 'requireIf:codeStyle,2'
        ];

        $message = [
            'old.length' => '旧密码长度不符合要求，格式为：6-16位',
            'password.require' => '支付密码不能为空',
            'password.length' => '支付密码长度不符合要求，格式为：6-16位',
            'confirmPassword.require' => '确认支付密码不能为空',
            'confirmPassword.length' => '确认支付密码长度不符合要求，格式为：6-16位',
        ];

        $data = [
            'old' => $this->request->param('old', ''),
            'password' => $this->request->param('password', ''),
            'confirmPassword' => $this->request->param('confirmPassword', ''),
            'codeStyle' => $this->request->param('codeStyle', 1),
            'smsCode' => $this->request->param('smsCode', ''),
            'googleCode' => $this->request->param('googleCode', ''),
        ];

        $result = $this->validate($data, $rules, $message);
        if ($result !== true) {
            $this->error($result);
        }

        $code = $data['codeStyle'] == 1 ? $data['smsCode'] : $data['googleCode'];
        $this->checkUserCode($data['codeStyle'], $code, 'changepassword');

        if ($this->auth->changepwd($data['password'], $data['old'])) {
            UserLog::addLog($this->auth->merchant_id, '修改登录密码');
            $this->success('修改密码成功，请妥善保管！');
        } else {
            $this->error('修改密码失败:' . $this->auth->getError());
        }


    }

    /**
     * 返回谷歌验证码的地址
     */
    public function getGoogleQrcode()
    {
        $ga = new GoogleAuthenticator();
        $user_info = $this->auth->getUserinfo();
        //如果用户绑定了的话则返回错误
        if ($user_info['googlebind'] == 1) {
            $this->error('绑定成功，无需重复绑定');
        }
        $secret = $ga->createSecret();

        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user_info['merchant_id'], $secret, config('site.name'));

        $data = [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret
        ];
        $this->success('获取绑定二维码成功', $data);
    }

    /**
     * 绑定谷歌验证码
     */
    public function bindGoogleQrcode()
    {

        $rules = [
            'secret' => 'require|length:16',
            'code' => 'require|number|length:6'
        ];
        $result = $this->validate($this->request->param(), $rules);
        if ($result !== true) {
            $this->error($result);
        }

        $secret = $this->request->param('secret');
        $code = $this->request->param('code');
        //开始验证输入的code是否正确
        if (validate_google_code($secret, $code)) {
            //给用户绑定上验证码
            \app\common\model\User::setGoogleSecret($secret, $this->auth->id);
            UserLog::addLog($this->auth->merchant_id, '绑定谷歌验证器');
            return $this->success('绑定谷歌验证器成功！');
        }
        return $this->error('请输入的验证码不匹配，请重新输入！');
    }

    /**
     * 清除谷歌验证器
     */
    public function clearGoogleBind()
    {

        $rules = [
            'code|谷歌验证码' => 'require|number|length:6'
        ];
        $data = [
            'code' => $this->request->param('code', '')
        ];
        $result = $this->validate($data, $rules);

        if (true !== $result) {
            $this->error($result);
        }


        $user_info = $this->auth->getUserinfo();
        //验证用户输入的信息是否正确
        if (validate_google_code($user_info['googlesecret'], $data['code'])) {
            \app\common\model\User::clearGoogleSecret($this->auth->id);
            UserLog::addLog($this->auth->merchant_id, '解绑谷歌验证器');
            return $this->success('谷歌验证器解绑成功，请重新绑定！');
        } else {
            return $this->error('您输入的验证码有误，请重新输入！');
        }

    }

    /**
     * 身份证照片上传
     */
    public function authUpload()
    {


        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $attachment = Attachment::get(['sha1' => $sha1, 'user_id' => $this->auth->id]);
        if (!is_null($attachment)) {

            $this->success('文件已经存在，无需重新上传！', [
                'id' => $attachment->id
            ]);
        }


        //自定义上传配置
        $upload = [
            /**
             * CDN地址
             */
            'cdnurl' => '',

            /**
             * 文件保存格式
             */
            'savekey' => '/uploads/{year}{mon}{day}/{filemd5}{.suffix}',
            /**
             * 最大可上传大小
             */
            'maxsize' => '1mb',
            /**
             * 可上传的文件类型
             */
            'mimetype' => 'jpg,png,bmp,jpeg,gif',
            /**
             * 是否支持批量上传
             */
            'multiple' => true,
        ];

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        //判断上传文件大小
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        $replaceArr = [
            '{year}' => date("Y"),
            '{mon}' => date("m"),
            '{day}' => date("d"),
            '{hour}' => date("H"),
            '{min}' => date("i"),
            '{sec}' => date("s"),
            '{random}' => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}' => $suffix,
            '{.suffix}' => $suffix ? '.' . $suffix : '',
            '{filemd5}' => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);

        //放到单独的目录里面
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/' . $uploadDir, $fileName);

        if ($splInfo) {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png'])) {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            $params = array(
                'admin_id' => 0,
                'user_id' => (int)$this->auth->id,
                'filesize' => $fileInfo['size'],
                'imagewidth' => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype' => $suffix,
                'imageframes' => 0,
                'mimetype' => $fileInfo['type'],
                'url' => $uploadDir . $splInfo->getSaveName(),
                'uploadtime' => time(),
                'storage' => 'local',
                'sha1' => $sha1,
            );
            $attachment = model("attachment");
            $attachment->data(array_filter($params));
            $attachment->save();
            \think\Hook::listen("upload_after", $attachment);

            $insert_id = $attachment->id;

            $this->success(__('Upload successful'), [
                'id' => intval($insert_id)
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    /**
     * 查看附件
     */
    public function attach()
    {

        $data = [
            'id' => $this->request->param('id')
        ];

        $rules = [
            'id|编号' => 'require|number'
        ];

        $result = $this->validate($data, $rules);
        if (true !== $result) {
            $this->error($result);
        }
        $file = Attachment::get([
            'id' => $data['id'],
            'user_id' => $this->auth->id
        ]);
        if ($file) {
            $file_path = ROOT_PATH . $file->url;
            if (is_file($file_path)) {
                //转换成base64给客户端
                $this->success('读取成功', [
                    'base64' => base64EncodeImage($file_path)
                ]);
            } else {
                $this->error(__('您访问的文件不存在!'), '', '404');
            }
        } else {
            $this->error(__('您无权访问此文件!'), '', '403');
        }

    }


    /**
     * 提交认证
     */
    public function auth()
    {

        $authModel = \app\common\model\UserAuth::get(['user_id' => $this->auth->id]);

        if ($authModel['status'] == '1') {
            $this->success('您已认证成功，无需重复提交');
        }

        $rules = [
            'name|姓名' => 'require|chsAlphaNum',
            'identity|身份证' => ['require', 'regex' => '/(^\d(15)$)|((^\d{18}$))|(^\d{17}(\d|X|x)$)/'],
            'industry|行业' => 'require|chsDash',
            'website|网址' => 'require|url',
            'picList|身份证照片' => 'require|array|length:2'
        ];

        $msg = [
            'picList.length' => '请上传身份证正反面照片'
        ];
        $data = [
            'name' => $this->request->param('name', ''),
            'identity' => $this->request->param('identity', ''),
            'industry' => $this->request->param('industry', ''),
            'website' => $this->request->param('website', ''),
            'picList' => $this->request->param('piclist/a', [])
        ];

        $result = $this->validate($data, $rules, $msg);
        if (true !== $result) {
            $this->error($result);
        }

        //过滤掉不合法的url
        foreach ($data['picList'] as $index => $pic) {
            if (!Validate::is($pic, 'integer')) {
                $data['picList'][$index] = 'invalid url';
            }
        }

        $data['user_id'] = $this->auth->id;
        $data['picList'] = serialize($data['picList']);
        $data['add_time'] = time();
        $data['status'] = 0;
        $data['check_msg'] = '';
        if (is_null($authModel)) {
            //验证成功的话就提交到数据库
            UserAuth::create($data);
        } else {
            $authModel->save($data);
        }
        UserLog::addLog($this->auth->merchant_id, '提交认证信息');
        return $this->success('申请认证成功,请等待审核！');

    }


    /**
     * 绑定手机号
     *
     */
    public function bindmobile()
    {
        $data = [
            'mobile' => $this->request->param('mobile', ''),
            'code' => $this->request->param('code', '')
        ];
        $rules = [
            'mobile|手机号' => ['require', 'regex' => '/^1[3-9]\d{9}$/', 'unique:user,mobile,' . $this->auth->id],
            'code|短信验证码' => 'require|integer|length:4'
        ];
        $result = $this->validate($data, $rules);

        if (true !== $result) {
            $this->error($result);
        }
        //检测是否绑定
        $user_info = \app\common\model\User::get($this->auth->id);
        if ($user_info['mobilebind'] == 1) {
            $this->error('您已经绑定过了，如需重新绑定请先解绑！');
        }

        $ret = Smslib::check($data['mobile'], $data['code'], 'bindphone');
        if (!$ret) {
            $this->error('短信验证码有误，请重新输入！');
        }
        $user_info->mobile = $data['mobile'];
        $user_info->mobilebind = 1;
        $user_info->save();
        UserLog::addLog($this->auth->merchant_id, '绑定手机号：' . $data['mobile']);
        $this->success('恭喜您，绑定成功！', ['mobile' => $data['mobile']]);
    }

    /**
     * 解绑手机
     */
    public function clearmobilebind()
    {

        $data = [
            'code' => $this->request->param('code', '')
        ];
        $rules = [
            'code|短信验证码' => 'require|integer|length:4'
        ];
        $result = $this->validate($data, $rules);

        if (true !== $result) {
            $this->error($result);
        }
        $user_info = $this->auth->getUserinfo();

        if ($user_info['mobilebind'] == 0) {
            $this->error('您还没有绑定手机号，请先绑定！');
        }
        $ret = Smslib::check($user_info['mobile'], $data['code'], 'clearmobilebind');
        if (!$ret) {
            $this->error('短信验证码有误，请重新输入！');
        }

        \app\common\model\User::update([
            'mobilebind' => 0
        ], ['id' => $this->auth->id]);
        Sms::flush($user_info['mobile'], 'clearmobilebind');
        UserLog::addLog($this->auth->merchant_id, '解绑手机号：' . $user_info['mobile']);
        $this->success('恭喜您，解绑成功！');

    }

    /**
     * 获取用户的列表数据
     */
    public function logs()
    {

        //查询条件
        $where = [
            'merchantid' => $this->auth->merchant_id
        ];
        //排序字段
        $orderField = $this->request->param('orderField', 'id');
        $sort = 'DESC';
        //分页字段
        $page = $this->request->param('page/d', 1);
        $pageLimit = 10;    //每页显示10条数据
        $offset = ($page - 1) * $pageLimit;

        //数据总数
        $total = UserLog::where($where)->count();
        $list = UserLog::where($where)
            ->order($orderField, $sort)
            ->limit($offset, $pageLimit)
            ->field(['ip', 'createtime', 'content'])
            ->select();
        $list = collection($list)->toArray();

        $this->success('获取数据成功！', [
            'total' => $total,
            'list' => $list,
            'limit' => $pageLimit
        ]);
    }

    /**
     * 会员充值
     */
    public function recharge()
    {

        $data = $this->request->only(['money','channel','bankcode']);
        $rules = [
            'money|充值金额'=>'require|number|egt:'.\config('site.minrecharge'),
            'channel|支付方式'=>'require|alphaDash|max:24',
            'bankcode|银行代码' => 'alphaNum|max:16'
        ];

        $messages = [
            'money.egt'=>'充值金额不符合系统要求。',
        ];


        $result = $this->validate($data, $rules,$messages);

        if (true !== $result) {
            $this->error($result);
        }

        $user = $this->auth->getUser();
        $merId = $user['merchant_id'];
        $orderId = 'CZ'.time();                     //充值单号
        $reqip = $this->request->ip();              //获取请求过来的ip地址
        $order_no = $merId . $orderId;              //商户订单号
        $sys_orderno = Order::createOrderNo();      //系统订单号
        $style = '1';                               //充值订单
        $returnUrl = url('/index/test/backurl','','',true);                            //同步跳转地址
        $notifyUrl = url('/index/test/notify','','',true);                             //异步通知地址
        $channel = $data['channel'];    //支付通道
        $orderAmt = $data['money'];     //金额
        $bankcode = empty($data['bankcode'])?'':$data['bankcode'];      //银行
        //需要的请求信息
        $data = [
            'merId'=>$merId,
            'orderId'=>$orderId,
            'orderAmt'=>$orderAmt,
            'channel'=>$channel,
            'desc'=>'recharge',
            'notifyUrl'=>$notifyUrl,
            'returnUrl'=>$returnUrl
        ];


        //查看商户是否锁定
        if ($user['status'] == 'hidden') {
            $msg = '商户已被锁定!';
            ApiLog::log($data,$msg);
            $this->error($msg);
        }

        //查看是否需要强制认证
        if (config('site.auth_switch') == '1') {
            if (empty($user->auth->status) || $user->auth->status != '1') {
                $msg = '请先完成认证!';
                ApiLog::log($data,$msg);
                $this->error($msg);
            }
        }

        //检查订单号是否重复
        if (!is_null(Order::getByOrderno($order_no))) {
            $msg = '订单号重复，请更换后重试!';
            ApiLog::log($data,$msg);
            $this->error($msg);
        }

        if (strstr($returnUrl, '?') !== false) {
            $msg = '同步地址不能带问号，请确认。';
            ApiLog::log($data,$msg);
            $this->error($msg);
        }

        //检查请求类型是否合法
//        $api_list = $user->getApiList2();

        $api_list = $user->getApiList2($orderAmt);

        if (empty($api_list[$channel])) {
            $msg = '该支付通道不存在，请联系商务!';
            ApiLog::log($data,$msg);
            $this->error($msg);
        }


        //网银如果传入bankcode 高于用户配置
        if ($channel == 'bank' && !empty($bankcode)) {
            $bankModel = Bank::get([
                'bankcode' => $bankcode,
                'status' => '1'
            ]);
            if (is_null($bankModel)) {
                $msg = '系统暂不支持该银行代码';
                ApiLog::log($data, $msg);
                $this->error($msg);
            }
            //是否设置了规则
            if (!empty($bankModel->api_rule_id)) {
                $channel_info = ApiRule::getChannelInfo($bankModel->api_rule_id, false);
                $apiTypeModel = ApiType::get(['code' => $channel]);
                $user_rate = $api_list['bank']['user_rate'];
                //规则信息下面需要
                $rule = [
                    'id' => $channel_info['info']['api_type_id'],
                    'account_id' => $channel_info['info']['api_account_ids']['id'],
                    'domain' => $apiTypeModel['domain'],
                    'account_weight' => $channel_info['info']['api_account_ids']['weight'],
                    'rule_type' => $channel_info['info']['type'], //规则
                    'rate' => $channel_info['rate_list'], //费率数组
                    'total' => $channel_info['total'],    //每天额度
                    'has' => $channel_info['has'],         //已用额度
                    'user_rate' => $user_rate
                ];
            } else {
                $rule = $api_list[$channel];
            }
        } else {
            $rule = $api_list[$channel];
        }

        $api_type_id = $rule['id'];
        $api_account_id = 0;
        $channel_rate = 0;
        $user_rate = 0;

        //如果单通道模式
        if ($rule['rule_type'] == '0') {

            if(empty($rule['account_id'][0])){
                $msg = '暂无可用通道，金额规则不匹配！';
                ApiLog::log($data, $msg);
                $this->error($msg);
            }


            $api_account_id = $rule['account_id'][0];
            $channel_rate = $rule['rate'][0];
            $user_rate = $rule['user_rate'] == '0' ? $channel_rate : $rule['user_rate'];
        }

        //顺序模式
        if ($rule['rule_type'] == '1') {

            $cache_key = 'ordercount:' . $user->merchant_id;
            $order_count = Cache::get($cache_key);
            if (!$order_count) {
                Cache::set($cache_key, 0, 86400); //一天过期
                $order_count = 0;
            }
            $account_index = $order_count % count($rule['account_id']);
            $api_account_id = $rule['account_id'][$account_index];
            $channel_rate = $rule['rate'][$account_index];
            $user_rate = $rule['user_rate'] == '0' ? $channel_rate : $rule['user_rate'];
            //增加
            Cache::inc($cache_key);
        }

        //随机轮询
        if ($rule['rule_type'] == '2') {
            $account_list = array_combine(array_values($rule['account_id']), array_values($rule['account_weight']));
            $api_account_id = Random::lottery($account_list);
            $rate_index = array_search($api_account_id, $rule['account_id']);
            $channel_rate = $rule['rate'][$rate_index];
            $user_rate = $rule['user_rate'] == '0' ? $channel_rate : $rule['user_rate'];
        }

        //获取到account 之后的逻辑
        $accountModel = ApiAccount::get($api_account_id);
        $upstreamModel = $accountModel->upstream;
        $channelModel = ApiChannel::get(['api_account_id' => $api_account_id, 'api_type_id' => $rule['id']]);


        /***
         * 检查限额
         */
        $today = Carbon::now()->toDateString();
        if($channelModel['today'] == $today && ($channelModel['todaymoney'] + $orderAmt) >= $channelModel['daymoney']){
            $msg = '该通道额度不足，请联系商务';
            ApiLog::log($data,$msg);
            $this->error($msg);
        }
        if ($channelModel['minmoney'] > 0 && $orderAmt < $channelModel['minmoney']) {
            $msg = '该通道最低充值金额为:' . $channelModel['minmoney'];
            ApiLog::log($data,$msg);
            $this->error($msg);
        }
        if ($channelModel['maxmoney'] > 0 && $orderAmt > $channelModel['maxmoney']) {
            $msg = '该通道最大充值金额为:' . $channelModel['maxmoney'];
            ApiLog::log($data,$msg);
            $this->error($msg);
        }



        //组装参数
        $api_upstream_id = $upstreamModel->id;
        $api_upstream_code = $upstreamModel->code;
        $domain = config('site.gateway');   //返回的域名

        //获取域名
        if ($accountModel['domain']) {
            $domain = $accountModel['domain'];
        } elseif ($rule['domain']) {
            $domain = $rule['domain'];
        }


        //提交给接口的参数
        $params = [
            'config' => $accountModel['params'], //配置参数
            'merId' => $merId,                //商户号
            'sys_orderno' => $sys_orderno,    //订单号
            'total_money' => $orderAmt,       //订单金额
            'channel' => $channel,            //通道代码
            'desc' => empty($desc) ? '' : $desc,                  //简单描述
            'user_id' => empty($userId) ? '' : $userId,              //快捷模式必须
            'ip' => $reqip,                                        //ip地址
            'domain' => $domain,                                 //地址信息
            'bankcode'=>empty($bankcode)?'':$bankcode,
            'notify_url'=>$domain.'/Pay/notify/code/'.$api_upstream_code,
            'return_url'=>$domain.'/Pay/return/code/'.$api_upstream_code,
        ];

        $api = loadApi($api_upstream_code);
        $result = $api->pay($params);
        if ($result[0] == 0) {
            $msg = $result[1];
            ApiLog::log($data,$msg);
            $this->error($msg);
        }
        $payurl = $result[1];       //支付地址
        ApiLog::log($data,'',$payurl);

        //使用bc函数改写
        $rate_money = bcmul($orderAmt,$user_rate);
        $rate_money = bcdiv($rate_money,100);
        //用户获得多少
        $hava_money = bcsub($orderAmt,$rate_money);

        //给上游的钱
        $upstream_money = 0;
        if (!empty($channelModel['upstream_rate']) && $channelModel['upstream_rate'] > 0) {
            $upstream_money = bcmul($orderAmt,$channelModel['upstream_rate']);
            $upstream_money = bcdiv($upstream_money,100);
        }

        $data = [
            'merchant_id' => $merId,      //商户号
            'orderno' => $order_no,       //订单号 商户id+他自己的订单号
            'sys_orderno' => $sys_orderno,    //系统订单号
            'total_money' => sprintf('%.2f', $orderAmt),   //订单金额
            'have_money' => $hava_money,     //获得金额
            'upstream_money' => $upstream_money,
            'style' => $style,
            'rate' => $user_rate,             //用户费率
            'channel_rate' => $channel_rate,  //上游费率
            'api_upstream_id' => $api_upstream_id,    //上游id
            'api_account_id' => $api_account_id,      //账号id
            'api_type_id' => $api_type_id,            //上游类型
            'req_info' => urldecode(http_build_query($data)),  //请求报文
            'req_ip'=>$reqip
        ];
        Order::create($data);


        //扫码跳转模式
        if (strstr($channel, 'sm')) {

            Cache::set('qr.'.$order_no,$payurl,3600);

            $payurl =  url('/index/ewm/show', ['orderno' => $order_no], '', config('site.url')) ;


//            $key = md5($order_no . $payurl . config('token.key'));
//            $payurl = url('/index/ewm/show', ['orderno' => $order_no], '', config('site.url')) . '?qr=' . urlencode($payurl) . '&key=' . $key;
        }

        $this->success('请求成功!', [
            'payurl' => $payurl,              //支付地址
            'orderno' => $order_no,           //订单号
            'sysorderno' => $sys_orderno      //系统订单号
        ]);
    }

    /**
     * 用户资金变动
     */
    public function moneylog()
    {

        $data = $this->request->only(['orderno', 'style','date']);

        $rules = [
            'orderno|订单号' => 'alphaDash',
            'style|类型' => 'in:1,2,3',
            'date|日期范围' => 'array'
        ];

        $result = $this->validate($data, $rules);
        if ($result !== true) {
            $this->error($result);
        }

        $where = [];

        if(!empty($data['orderno'])){
            $where['orderno'] = ['like','%'.$data['orderno'].'%'];
        }

        if(!empty($data['style'])){
            $where['style'] = $data['style'];
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

        $userModel = $this->auth->getUser();
        //数据总数
        $total = $userModel->moneylog()->where($where)->count();

        $list = $userModel->moneylog()
            ->where($where)
            ->order($orderField, $sort)
            ->limit($offset, $pageLimit)
            ->select();

        foreach ($list as $k => $v) {
            $v->hidden(['user_id']);
        }
        $list = collection($list)->toArray();

        $extend = [];
        $this->success('获取数据成功！', [
            'total' => $total,
            'list' => $list,
            'limit' => $pageLimit,
            'extend'=>$extend
        ]);

    }

}
