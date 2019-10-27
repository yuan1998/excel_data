<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeiboSpend extends Model
{
    public static $excelFields = [
        "时间"        => 'date',
        "广告计划"      => 'advertiser_plan',
        "投放场景"      => 'delivery_scenario',
        "投放目标"      => 'target',
        "曝光量"       => 'show',
        "曝光量_粉丝"    => 'show_fans',
        "互动数"       => 'interactive',
        "互动数_粉丝"    => 'interactive_fans',
        "互动率"       => 'interactive_rate',
        "互动率_粉丝"    => 'interactive_rate_fans',
        "花费"        => 'spend',
        "花费_粉丝"     => 'spend_fans',
        "千次曝光成本"    => 'thousand_times_show_price',
        "千次曝光成本_粉丝" => 'thousand_times_show_price_fans',
        "单次互动成本"    => 'once_interactive_price',
        "单次互动成本_粉丝" => 'once_interactive_price_fans',
        "质量分"       => 'quality_score',
        "质量分_粉丝"    => 'quality_score_fans',
        "负面指数"      => 'negative',
        "负面指数_粉丝"   => 'negative_fans',
    ];

    protected $fillable = [
        'type',
        'date',
        'advertiser_account',
        'advertiser_plan',
        'show',
        'show_fans',
        'interactive',
        'interactive_fans',
        'interactive_rate',
        'interactive_rate_fans',
        'spend',
        'spend_fans',
        'thousand_times_show_price',
        'thousand_times_show_price_fans',
        'once_interactive_price',
        'once_interactive_price_fans',
        'quality_score',
        'quality_score_fans',
        'negative',
        'negative_fans',
    ];

}
