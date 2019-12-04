<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class BaiduData extends Model
{
    public static $excelFields = [
        "本次访问时间"   => 'cur_access_time',
        "来源省市"     => 'city',
        "对话类型"     => 'dialog_type',
        "访客名称"     => 'visitor_name',
        "客户类型"     => 'visitor_type',
        "初始接待客服"   => 'first_customer',
        "本次最初访问网页" => 'first_url',
        "关键词"      => 'keyword',
        "来源ip"     => 'ip',
        "来源网页"     => 'url',
        "会话ID"     => 'dialog_id',
        "访客ID"     => 'visitor_id',
        "首次访问时间"   => 'first_access_date',
        "上次访问时间"   => 'previous_access_date',
        "开始对话时间"   => 'start_dialog_date',
        "所有关键词"    => 'all_keyword',
        "搜索引擎"     => 'search_engine',
        "对话网址"     => 'dialog_url',
        "对话关键词"    => 'dialog_keyword',
        "竞价词"      => 'bidding_keyword',
        "站点"       => 'site',
        "线索"       => 'clue',
        "数据类型"     => 'type',
    ];

    protected $fillable = [
        'cur_access_time',
        'city',
        'dialog_type',
        'visitor_name',
        'visitor_type',
        'first_customer',
        'first_url',
        'keyword',
        'ip',
        'url',
        'dialog_id',
        'visitor_id',
        'first_access_date',
        'previous_access_date',
        'start_dialog_date',
        'all_keyword',
        'search_engine',
        'dialog_url',
        'dialog_keyword',
        'bidding_keyword',
        'site',
        'type',
        'form_type',
        'department_id',
        'code',
    ];

    public static $ChannelCategory = [
        'A10' => '百度竞价',
        'A20' => '搜狗',
        'A30' => '神马',
        'A60' => '百度信息流',
        'A8'  => 'oppo',
    ];


    public static function checkCodeIs($str)
    {
        if (!$str) return null;

        if (preg_match('/A6/', $str)) {
            return 1;
        }
        if (preg_match('/A30/', $str)) {
            return 7;
        }
        if (preg_match('/A20/', $str)) {
            return 6;
        }
        if (preg_match('/A10/', $str)) {
            return 5;
        }
        if (preg_match('/A8/', $str)) {
            return 8;
        }

    }


    public function clues()
    {
        return $this->hasMany(BaiduClue::class, 'baidu_id', 'id');
    }

    /**
     * 关联 项目
     * @return MorphToMany
     */
    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    /**
     * 关联 科室
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(DepartmentType::class, 'department_id', 'id');
    }

}
