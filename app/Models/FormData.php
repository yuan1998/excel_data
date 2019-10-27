<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormData extends Model
{
    protected $fillable = [
        'phone',
        'weibo_id',
        'baidu_id',
        'feiyu_id',
        'archive_type',
        'form_type',
        'date',
        'data_type',
        'department_id',
        'type',
        'project_id',
    ];

    public static $FormTypeList = [
        1 => '百度信息流',
        2 => '微博',
        3 => '头条',
        4 => '抖音',
        5 => '百度竞价',
        6 => '搜狗',
        7 => '神马',
    ];

    public static $FormCountDataFormat = [
        'form_count'   => 0,
        'is_archive-0' => 0,
        'is_archive-1' => 0,
        'intention-0'  => 0,
        'intention-1'  => 0,
        'intention-2'  => 0,
        'intention-3'  => 0,
        'intention-4'  => 0,
        'intention-5'  => 0,
        'intention-6'  => 0,
    ];

    public function phones()
    {
        return $this->hasMany(FormDataPhone::class);
    }

    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentType::class, 'department_id', 'id');
    }

}
