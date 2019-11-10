<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecallLog extends Model
{
    protected $fillable = [
        'tags',
        'comment',
        'weibo_user_id',
        'weibo_form_id',
    ];

    public function changeBy()
    {
        return $this->belongsTo(WeiboUser::class, 'weibo_user_id', 'id')->withDefault([
            'username' => 'Admin-管理员',
        ]);
    }

    public static function logInit()
    {
        RecallLog::truncate();
        WeiboFormData::query()
            ->whereNotNull('recall_date')
            ->get()
            ->each(function ($item) {
                RecallLog::create([
                    'tags'          => $item->tags,
                    'comment'       => $item->comment,
                    'weibo_user_id' => $item->weibo_user_id,
                    'weibo_form_id' => $item->id,
                ]);
            });
    }

}
