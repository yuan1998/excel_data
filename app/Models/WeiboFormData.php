<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WeiboFormData extends Model
{
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

        'tags',
        'remark',
        'weibo_user_id',
        'dispatch_date',
        'upload_date',
        'recall_date',
    ];

    public static $TagList = [
        0 => '未标记',
        1 => '转微',
        2 => '未接通',
        3 => '空号',
        4 => '无需求',
        5 => '重复建档',
        6 => '已到院',
    ];

    public function weiboUser()
    {
        return $this->belongsTo(WeiboUser::class, 'weibo_user_id', 'id');
    }

    public static function myCreate($condition, $attributes)
    {
        $query = static::query();
        foreach ($condition as $key => $value) {
            $query->where($key, $value);
        }
    }

    public function dispatchItem()
    {
        $id = WeiboUser::newDispatchData();
        if ($id) {
            $this->weibo_user_id = $id;
            $this->dispatch_date = Carbon::now()->toDateTimeString();
            $this->save();
        }
    }

    public static function unallocated()
    {
        if (WeiboUser::newDispatchData()) {
            static::query()
                ->whereNull('weibo_user_id')
                ->get()
                ->each(function ($item) {
                    $item->dispatchItem();
                });
        }
    }
}
