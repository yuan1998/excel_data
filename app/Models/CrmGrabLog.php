<?php

namespace App\models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;

class CrmGrabLog extends Model
{
    protected $fillable = [
        'status',
        'type',
        'model_type',
        'start_date',
        'end_date',
        'name',
    ];

    public static $typeList = [
        'zx' => '整形医院',
        'kq' => '口腔医院',
    ];

    public static $statusList = [
        0 => '队列中',
        1 => '查询中',
        2 => '查询完成',
        3 => '查询失败',
    ];


    public static $modelTypeList = [
        'arrivingData'     => '到院数据',
        'billAccountData'  => '业绩数据',
        'tempCustomerData' => '临客数据',
    ];


    public static function generate($type, $model_type, $start, $end)
    {
        $exists = static::query()
            ->whereIn('status', [0, 1])
            ->where('type', $type)
            ->where('model_type', $model_type)
            ->where('start_date', $start)
            ->where('end_date', $end)
            ->exists();

        if (!$exists) {
            $modelName = static::$modelTypeList[$model_type];
            $typeName  = static::$typeList[$type];

            return static::create([
                'name'       => $typeName . '_' . $modelName . '_' . $start . '-' . $end,
                'type'       => $type,
                'model_type' => $model_type,
                'start_date' => $start,
                'end_date'   => $end,
            ]);
        }
        return null;
    }

}
