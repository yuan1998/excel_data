<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ExportDataLog extends Model
{
    protected $fillable = [
        'name',
        'file_name',
        'path',
        'request_data',
        'run_time',
        'log',
    ];

    public static $staticList = [
        0 => '队列中',
        1 => '生成中',
        2 => '生成成功',
        3 => '生成失败',
    ];

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
        $dateName       = implode('_', $data['dates']);
        $type           = CrmGrabLog::$typeList[$data['type']];
        return "[$type]_[{$channelName}]_[{$departmentName}]_[{$dateName}]";
    }

    public static function generate($data)
    {
        $name     = static::makeName($data);
        $date     = Carbon::today()->toDateString();
        $dateName = implode('_', $data['dates']);

        return static::create([
            'name'         => $name,
            'path'         => '/exports/' . $date . '/',
            'file_name'    => $name . '.xlsx',
            'request_data' => json_encode($data),
        ]);
    }
}
