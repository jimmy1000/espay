<?php

namespace app\admin\model;

use think\Model;


class ApiRule extends Model
{

    // 表名
    protected $name = 'api_rule';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text'
    ];


    public function getTypeList()
    {
        return ['0' => __('Type 0'), '1' => __('Type 1'), '2' => __('Type 2')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function apitype()
    {
        return $this->belongsTo('ApiType', 'api_type_id', [], 'LEFT')->setEagerlyType(0);
    }

    public function setApiAccountIdsAttr($value)
    {

        $field_array = [];

        foreach ($value['id'] as $k => $v) {
            if (empty($v)) {
                continue;
            }
            array_push($field_array, $v . ':' . $value['weight'][$k]);
        }
        if (empty($field_array)) {
            return '';
        }
        return implode(',', $field_array);
    }

    public function getApiAccountIdsAttr($value)
    {
        if (!$value) return [
            'id' => [],
            'weight' => []
        ];
        $field_array = explode(',', $value);
        $result = [];
        foreach ($field_array as $k => $v) {
            $tmp_array = explode(":", $v);
            $result['id'][] = $tmp_array[0];
            $result['weight'][$tmp_array[0]] = $tmp_array[1];
        }
        return $result;
    }


    public static function getListByApiType($type_id)
    {
        $list = self::where([
            'api_type_id' => $type_id
        ])->select();
        return collection($list)->toArray();
    }

}
