<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ExportDataLog extends Model
{
    protected $fillable = [
        'name',
        'file_name',
        'path',
        'request_data',
        'run_time',
        'log',
        'data_type',
    ];

    public static $staticList = [
        0 => '队列中',
        1 => '生成中',
        2 => '生成成功',
        3 => '生成失败',
    ];

    public static $typeList = [
        'xxl_data_excel'     => '信息流数据表格',
        'baidu_plan'         => '百度计划数据表格',
        'sanfang_data_excel' => '三方数据表格',
    ];

    public static function setAllToType($type)
    {
        static::query()->update([
            'data_type' => $type
        ]);

    }

    public static function makeDatesName($dates)
    {
        return collect($dates)->map(function ($date) {
            return Carbon::parse($date)->toDateString();
        })->join('_');
    }

    public static function makeName($data)
    {
        $departmentName = DepartmentType::query()
            ->whereIn('id', $data['department_id'])
            ->get('title')
            ->pluck('title')
            ->implode(',');
        $channelName    = Channel::query()
            ->whereIn('id', $data['channel_id'])
            ->get('title')
            ->pluck('title')
            ->implode(',');
        $dateName       = static::makeDatesName($data['dates']);
        $type           = CrmGrabLog::$typeList[$data['type']];
        return time() . "_[$type]_[{$channelName}]_[{$departmentName}]_[{$dateName}]";
    }


    public static function baseGenerate($data)
    {
        $name = static::makeName($data);
        $date = Carbon::today()->toDateString();

        return static::create([
            'name'         => $name,
            'data_type'    => $data['data_type'],
            'path'         => '/exports/' . $date . '/',
            'file_name'    => $name . '.xlsx',
            'request_data' => json_encode($data),
        ]);
    }

    public static function baiduPlanGenerate($data)
    {
        $dateName = static::makeDatesName($data['dates']);
        $name     = time() . '_百度计划报告_' . $dateName;
        $date     = Carbon::today()->toDateString();

        return static::create([
            'data_type'    => $data['data_type'],
            'name'         => $name,
            'path'         => '/exports/' . $date . '/',
            'file_name'    => $name . '.xlsx',
            'request_data' => json_encode($data),
        ]);
    }

    public static function sanfangGenerate($data)
    {
        $dateName = static::makeDatesName($data['dates']);
        $name     = time() . '_三方数据报表_' . $dateName;
        $date     = Carbon::today()->toDateString();
        
        return static::create([
            'data_type'    => $data['data_type'],
            'name'         => $name,
            'path'         => '/exports/' . $date . '/',
            'file_name'    => $name . '.xlsx',
            'request_data' => json_encode($data),
        ]);
    }

    public static function generate($data)
    {
        $dataType = Arr::get($data, 'data_type', null);
        switch ($dataType) {
            case 'baidu_plan':
                return static::baiduPlanGenerate($data);
                break;
            case 'xxl_data_excel':
                return static::baseGenerate($data);
                break;
            case 'sanfang_data_excel':
                return static::sanfangGenerate($data);
                break;

        }
    }
}
