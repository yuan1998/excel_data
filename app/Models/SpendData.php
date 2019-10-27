<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpendData extends Model
{
    protected $fillable = [
        'date',
        'spend',
        'spend_type',
        'show',
        'click',
        'baidu_id',
        'weibo_id',
        'feiyu_id',
        'spend_name',
        'project_id',
        'department_id',

    ];

    public static $SpendCountDataFormat = [
        'spend' => 0,
        'click' => 0,
        'show'  => 0,
    ];

    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentType::class, 'department_id', 'id');
    }


}
