<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeiyuSpend extends Model
{
    public static $excelFields = [
        "时间"          => 'date',
        "广告组id"       => 'advertiser_id',
        "广告组"         => 'advertiser_name',
        "展示数"         => 'show',
        "点击数"         => 'click',
        "点击率(%)"      => 'click_rate',
        "平均点击单价(元)"   => 'average_click_price',
        "平均千次展现费用(元)" => 'average_thousand_times_show_price',
        "总花费(元)"      => 'spend',
        "转化数"         => 'conversion',
        "转化成本"        => 'conversion_price',
        "转化率"         => 'conversion_rate',
        "深度转化次数"      => 'deep_conversion',
        "深度转化成本"      => 'deep_conversion_price',
        "深度转化率"       => 'deep_conversion_rate',
    ];

    protected $fillable = [
        'type',
        'date',
        'advertiser_id',
        'advertiser_name',
        'show',
        'click',
        'click_rate',
        'average_click_price',
        'average_thousand_times_show_price',
        'spend',
        'conversion',
        'conversion_price',
        'conversion_rate',
        'deep_conversion',
        'deep_conversion_price',
        'deep_conversion_rate',
    ];
}
