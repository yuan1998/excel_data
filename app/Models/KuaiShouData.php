<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuaiShouData extends Model
{

    protected $fillable = [
        'clue_id',
        'name',
        'phone',
        'clue_status',
        'call_status',
        'follow_status',
        'tags',
        'date',
        'page_name',
        'form_component',
        'comment',
        'description',
    ];

    public static $fields = [
        'clue_id'        => "线索id",
        'name'           => "姓名",
        'phone'          => "电话",
        'clue_status'    => "线索状态",
        'call_status'    => "通话状态",
        'follow_status'  => "跟进状态",
        'tags'           => "标签",
        'date'           => "收集时间",
        'page_name'      => "落地页",
        'form_component' => "表单",
        'comment'        => "备注",
        'description'    => "详情",
    ];
}
