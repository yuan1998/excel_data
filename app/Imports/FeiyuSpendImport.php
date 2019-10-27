<?php

namespace App\Imports;

use App\Helpers;
use App\Models\AccountReturnPoint;
use App\Models\BaiduData;
use App\Models\BaiduSpend;
use App\Models\FeiyuSpend;
use App\Models\ProjectType;
use App\Models\SpendData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class FeiyuSpendImport implements ToCollection
{

    /**
     * FeiyuSpendImport constructor.
     */
    public function __construct()
    {

    }


    public function checkType($str)
    {
        if (preg_match("/B/", $str)) {
            return 3;
        }
        if (preg_match("/D/", $str)) {
            return 4;
        }
        return 0;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, FeiyuSpend::$excelFields);

        collect($data)->filter(function ($item) {
            return isset($item['date'])
                && isset($item['advertiser_id']);
        })->each(function ($item) {
            $departmentType = Helpers::checkDepartment($item['advertiser_name']);

            if (!$departmentType) {
                throw new \Exception('无法判断科室:' . $item['advertiser_name'] . '。请手动删除或者修改为可识别的科室.');
            }

            $projectType = Helpers::checkDepartmentProject($departmentType, $item['advertiser_name'], 'spend_keyword');

            if (count($projectType) > 1) {
                throw new \Exception('识别为多个病种:' . $item['advertiser_name']);
            }


            $item['advertiser_id'] = trim($item['advertiser_id']);
            $spendType             = $this->checkType($item['advertiser_name']);

            if (!$spendType)
                throw new \Exception('错误的数据,无法分辨数据类型. ' . $item['advertiser_name']);
            $item['date'] = Carbon::parse($item['date'])->toDateString();

            $rebate = Helpers::checkFormTypeRebate($spendType, $item['advertiser_name']);
            if (!$rebate) {
                throw new \Exception('错误的数据,无法获取返点信息. ' . $item['advertiser_name']);
            }

            $feiyu = FeiyuSpend::updateOrCreate([
                'date'          => $item['date'],
                'advertiser_id' => $item['advertiser_id'],
            ], $item);

            $spend = SpendData::updateOrCreate([
                'feiyu_id' => $feiyu->id,
            ], [
                'department_id' => $departmentType->id,
                'date'          => $item['date'],
                'spend_name'    => $item['advertiser_name'],
                'click'         => $item['click'],
                'show'          => $item['show'],
                'spend'         => (float)$item['spend'] / $rebate,
                'spend_type'    => $spendType,
                'project_id'    => 0,
            ]);

            $spend->projects()->sync($projectType);
        });
    }
}
