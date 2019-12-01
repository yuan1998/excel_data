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
    public $count = 0;

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
                && isset($item['advertiser_id'])
                && isset($item['advertiser_name']);
        })->each(function ($item) {
            $name = $item['advertiser_name'];
            // 判断 所属科室 是否存在 , 如果不存在,则报错
            $departmentType = Helpers::checkDepartment($name);
            if (!$departmentType) {
                throw new \Exception('无法判断科室:' . $name . '。请手动删除或者修改为可识别的科室.');
            }

            // 判断 所属科室 下的病种.如果不存在或者匹配了多个,判断为其他
            $projectType = Helpers::checkDepartmentProject($departmentType, $name, 'spend_keyword');

            // 去除 id 两端的空格
            $item['advertiser_id'] = trim($item['advertiser_id']);
            $item['type']          = $departmentType->type;

            // 确认平台类型, 如果不在预测范围内,报错.
            $item['spend_type'] = $this->checkType($name);
            if (!$item['spend_type'])
                throw new \Exception('错误的数据,无法分辨数据类型. ' . $name);

            // 简化时间
            $item['date'] = Carbon::parse($item['date'])->toDateString();

            $feiyu = FeiyuSpend::updateOrCreate([
                'date'          => $item['date'],
                'advertiser_id' => $item['advertiser_id'],
            ], $item);

            $account  = Helpers::formDataCheckAccount($item, 'advertiser_name', 'spend_type', true);
            $offSpend = (float)$item['spend'];
            if ($account) {
                $offSpend = $offSpend * (float)$account['rebate'];
            }

            $spend = SpendData::updateOrCreate([
                'model_id'   => $feiyu->id,
                'model_type' => FeiyuSpend::class
            ], [
                'type'            => $item['type'],
                'department_id'   => $departmentType->id,
                'date'            => $item['date'],
                'spend_name'      => $name,
                'click'           => $item['click'],
                'show'            => $item['show'],
                'spend'           => $item['spend'],
                'off_spend'       => $offSpend,
                'spend_type'      => $item['spend_type'],
                'account_id'      => $account ? $account['id'] : null,
                'account_keyword' => $name,
            ]);

            $spend->projects()->sync($projectType);
            $this->count++;
        });
    }
}
