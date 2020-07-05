<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;

class Consultant extends Model
{
    protected $fillable = [
        'type',
        'name',
        'department_id',
        'keyword',
        'crm_id',
    ];

    public function consultantGroup()
    {
        return $this->belongsToMany(ConsultantGroup::class, 'consultant_group_id', 'consultant_id', 'group_id');
    }

    public function weiboUser()
    {
        return $this->hasOne(WeiboUser::class, 'consultant_id', 'id');
    }

    public static function fixKeyword()
    {
        static::query()
            ->whereNull('keyword')
            ->select(['id','name'])
            ->get()
            ->each(function ($item) {
                $item->update([
                    'keyword' => Helpers::consultantNameParse($item['name']),
                ]);
            });

    }

}
