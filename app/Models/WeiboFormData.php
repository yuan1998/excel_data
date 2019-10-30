<?php

namespace App\Models;

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

        'remark',
        'weibo_user_id',
        'upload_date',
        'recall_date',
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

    public static function unallocated()
    {
        if (WeiboUser::newDispatchData()) {
            static::query()
                ->whereNull('weibo_user_id')
                ->get()
                ->each(function ($item) {
                    $id = WeiboUser::newDispatchData();
                    if ($id) {
                        $item->weibo_user_id = $id;
                        $item->save();
                    }
                });
        }
    }
}
