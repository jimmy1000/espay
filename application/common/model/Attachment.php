<?php

namespace app\common\model;

use think\Model;

class Attachment extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    public function setUploadtimeAttr($value)
    {
        return is_numeric($value) ? $value : strtotime($value);
    }

    protected static function init()
    {
        // 如果已经上传该资源，则不再记录
        self::beforeInsert(function ($model) {
            if (self::where('url', '=', $model['url'])->find()) {
                return false;
            }
        });
    }

    /**
     * 根据sha1查看文件是否上传了如果上传了就不传了
     *
     * @param $sha1
     */
    public static function isExists($sha1 = ''){
        return !is_null(self::get(
            ['sha1'=>$sha1]
        ));
    }
}
