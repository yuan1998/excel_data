<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    public static $FormTypeList = [
        0 => '未知类型',
        1 => '百度信息流',
        2 => '微博',
        4 => '抖音',
        3 => '头条',
    ];

    protected $fillable = [
        'title',
        'form_type',
    ];

    public function mediums()
    {
        return $this->belongsToMany(MediumType::class, 'channel_has_medium', 'channel_id', 'medium_id');
    }

    public function accounts()
    {
        return $this->hasMany(AccountData::class, 'channel_id', 'id');
    }

    public function setFormTypeAttribute($formType)
    {
        $this->attributes['form_type'] = trim(implode($formType, ','), ',');
    }


    public function checkAccount($type, $code , $getOfItem = false)
    {
        $channelId = $this->id;

        $accounts = AccountData::query()
            ->where('channel_id', $channelId)
            ->where('type', $type)
            ->get();

        return $accounts ? Helpers::accountValidationString($accounts, $code, 'keyword',$getOfItem) : null;
    }

}
