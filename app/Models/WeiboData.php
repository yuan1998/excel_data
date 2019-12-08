<?php

namespace App\Models;

use App\Clients\KqClient;
use App\Clients\ZxClient;
use App\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class WeiboData extends Model
{
    public static $fields = [
        '建档'   => 'is_archive',
        '意向度'  => 'intention',
        '数据类型' => 'type',
        '到院类型' => 'arriving_type',
    ];

    public static $excelFields = [
        "序号"   => 'weibo_id',
        "项目ID" => 'project_id',
        "项目名称" => 'project_name',
        "提交时间" => 'post_date',
        "姓名"   => 'name',
        "电话"   => 'phone',
        "分组类型" => 'category_type',
        "反馈"   => 'feedback',
        "备注"   => 'comment',
        "微信号"  => 'weixin',
        "导出时间" => 'export_date',
    ];

    protected $fillable = [
        'weibo_id',
        'project_id',
        'project_name',
        'post_date',
        'name',
        'phone',
        'category_type',
        'feedback',
        'comment',
        'weixin',
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

}
