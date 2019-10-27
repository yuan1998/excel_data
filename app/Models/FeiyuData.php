<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;

class FeiyuData extends Model
{

    public static $fields = [
        '建档'   => 'is_archive',
        '意向度'  => 'intention',
        '数据类型' => 'type',
        '到院类型' => 'arriving_type',
    ];

    public static $excelFields = [
        "线索id"   => 'clue_id',
        "姓名"     => 'name',
        "电话"     => 'phone',
        "线索状态"   => 'clue_status',
        "所属人"    => 'owner',
        "通话状态"   => 'call_status',
        "标签"     => 'tag',
        "线索类型"   => 'clue_type',
        "流量来源"   => 'source',
        "转化状态"   => 'conversion_status',
        "推广链接"   => 'sponsored_link',
        "微信号"    => 'weixin',
        "QQ号"    => 'qq',
        "邮箱"     => 'email',
        "性别"     => 'gender',
        "年龄"     => 'age',
        "日期"     => 'date',
        "城市"     => 'city',
        "详细地址"   => 'address',
        "组件ID"   => 'component_id',
        "组件名称"   => 'component_name',
        "市场活动ID" => 'activity_id',
        "市场活动名称" => 'activity_name',
        "备注"     => 'remarks',
        "留言"     => 'comment',
        "跟进记录"   => 'follow_logs',
        "创建时间"   => 'post_date',
        "广告主ID"  => 'advertiser_id',
        "广告主名称"  => 'advertiser_name',
        "所在地"    => 'location',
        "门店id"   => 'store_id',
        "门店名称"   => 'store_name',
    ];

    protected $fillable = [
        'clue_id',
        'name',
        'phone',
        'clue_status',
        'owner',
        'call_status',
        'tag',
        'clue_type',
        'source',
        'conversion_status',
        'sponsored_link',
        'weixin',
        'qq',
        'email',
        'gender',
        'age',
        'date',
        'city',
        'address',
        'component_id',
        'component_name',
        'activity_id',
        'activity_name',
        'remarks',
        'comment',
        'follow_logs',
        'post_date',
        'advertiser_id',
        'advertiser_name',
        'location',
        'store_id',
        'store_name',
        'is_archive',
        'intention',
        'type',
        'arriving_type',
    ];

    public function getDate()
    {
        return $this->post_date;
    }

    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    public static function recheckProjects()
    {
        $projectTypes = ProjectType::all()->pluck('keyword', 'id');

        static::all()->each(function ($item) use ($projectTypes) {
            $item->projects()->sync(Helpers::projectTypeCheck($projectTypes, $item->activity_name));
        });
    }
}
