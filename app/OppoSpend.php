<?php

namespace App;

use App\Models\BaiduSpend;
use App\Models\SpendData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OppoSpend extends Model
{
    public static $excelFields = [
        "时间"   => 'date',
        "计划ID" => 'plan_id',
        "计划名称" => 'plan_name',
        "曝光量"  => 'show',
        "下载量"  => 'download',
        "下载率"  => 'download_rate',
        "点击量"  => 'click',
        "点击率"  => 'click_rate',
        "消耗金额" => 'spend',
        "下载均价" => 'download_spend',
        "点击均价" => 'click_spend',
        "ECPM" => 'ecpm',
    ];

    protected $fillable = [
        'date',
        'plan_id',
        'plan_name',
        'show',
        'download',
        'download_rate',
        'click',
        'click_rate',
        'spend',
        'download_spend',
        'click_spend',
        'ecpm',
        'type',
        'spend_type',
        'code',
    ];

    public static function handleExcelDataParser($item)
    {
        $item['date']       = Carbon::parse($item['date'])->toDateString();
        $code               = $item['code'] = $item['plan_name'];
        $item['spend_type'] = 8;
        if (!$departmentType = Helpers::checkDepartment($code))
            throw new \Exception('无法判断科室:' . $code . '。请手动删除或者修改为可识别的科室.');

        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code, 'spend_keyword');
        $item['department_type'] = $departmentType;
        $item['type']            = $departmentType->type;

        return $item;
    }

    public static function handleExcelData($data)
    {
        $count = 0;
        foreach ($data as $item) {
            $item = static::handleExcelDataParser($item);

            $oppo = static::updateOrCreate([
                'plan_id' => $item['plan_id'],
                'date'    => $item['date'],
            ], $item);


            $spend = SpendData::updateOrCreate([
                'model_id'   => $oppo->id,
                'model_type' => OppoSpend::class
            ], SpendData::parseMakeSpendData($item));

            $spend->projects()->sync($item['project_type']);
            $count++;
        }
        return $count;
    }

}
