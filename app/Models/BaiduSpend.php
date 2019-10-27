<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaiduSpend extends Model
{
    public static $excelFields = [
        '日期'     => 'date',
        '推广计划'   => 'promotion_plan',
        '推广计划ID' => 'promotion_plan_id',
        '展现'     => 'show',
        '点击'     => 'click',
        '消费'     => 'spend',
    ];

    protected $fillable = [
        'date',
        'promotion_plan',
        'promotion_plan_id',
        'show',
        'click',
        'spend',
        'type',
    ];
}
