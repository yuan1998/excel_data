<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduSpend;
use App\Models\ProjectType;
use App\Models\SpendData;
use App\Models\WeiboSpend;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class WeiboSpendImport implements ToCollection
{

    /**
     * WeiboSpendImport constructor.
     */
    public function __construct()
    {
    }


    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, WeiboSpend::$excelFields);

        collect($data)->filter(function ($item) {
            return isset($item['date'])
                && isset($item['advertiser_plan'])
                && $item['advertiser_plan'] != '-';
        })->each(function ($item) {
            $departmentType = Helpers::checkDepartment($item['advertiser_plan']);

            if (!$departmentType)
                throw new \Exception('无法判断科室:' . $item['advertiser_plan']);

            $projectType = Helpers::checkDepartmentProject($departmentType, $item['advertiser_plan'], 'spend_keyword');

            if (count($projectType) > 1) {
                throw new \Exception('识别为多个病种:' . $item['advertiser_plan']);
            }

            $weibo = WeiboSpend::updateOrCreate([
                'date'            => $item['date'],
                'advertiser_plan' => $item['advertiser_plan'],
            ], $item);

            $spend = SpendData::updateOrCreate([
                'weibo_id' => $weibo->id,
            ], [
                'department_id' => $departmentType->id,
                'date'          => $item['date'],
                'click'         => $item['interactive'],
                'spend_name'    => $item['advertiser_plan'],
                'show'          => $item['show'],
                'spend'         => $item['spend'],
                'spend_type'    => 2,
                'project_id'    => 0,
            ]);

            $spend->projects()->sync($projectType);

        });


    }
}
