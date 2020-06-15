<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuaiShouSpend extends Model
{
    public static $fields = [
        "日期"         => 'date',
        "广告计划"       => 'advertiser_name',
        "花费"         => 'spend',
        "封面曝光数"      => 'cover_show',
        "封面点击数"      => 'click',
        "素材曝光数"      => 'material_show',
        "行为数"        => 'behavior',
        "封面点击率"      => 'cover_click_rate',
        "行为率"        => 'behavior_rate',
        "平均千次封面曝光花费" => 'svg_cover_show_cost',
        "平均封面点击单价"   => 'svg_cover_click_cost',
        "平均行为单价"     => 'svg_behavior_cost',
    ];

    protected $fillable = [
        'type',
        'code',
        'department_id',

        'date',
        'advertiser_name',
        'spend',
        'cover_show',
        'click',
        'material_show',
        'behavior',
        'cover_click_rate',
        'behavior_rate',
        'svg_cover_show_cost',
        'svg_cover_click_cost',
        'svg_behavior_cost',
    ];


}
