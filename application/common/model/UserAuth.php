<?php

namespace app\common\model;

use think\Model;

class UserAuth extends Model
{



    // 表名
    protected $name = 'user_auth';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'add_time_text',
        'check_time_text'
    ];


    //定义图片的获取器
    public function getPicListAttr($value){
        $pic_list = unserialize($value);

        $result = [];
        //直接返回base64编码给客户
        $pic_list = implode(',',$pic_list);
        $attchment_list = Attachment::where('id','in',$pic_list)->select();


        foreach ($attchment_list as $attach){
            $file_path = ROOT_PATH.$attach['url'];
            if(is_file($file_path)){
                array_push($result,base64EncodeImage($file_path));
            }
        }
        return $result;
    }


    public function getStatusList()
    {

        return ['0' => '未审核', '1' => '审核成功', '2' => '审核失败'];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAddTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['add_time']) ? $data['add_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCheckTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['check_time']) ? $data['check_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAddTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCheckTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
