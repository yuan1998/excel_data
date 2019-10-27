<?php

namespace App\Models;

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


    public function setFormTypeAttribute($formType)
    {
        $this->attributes['form_type'] = trim(implode($formType, ','), ',');
    }

}
