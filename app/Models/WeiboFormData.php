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
}
