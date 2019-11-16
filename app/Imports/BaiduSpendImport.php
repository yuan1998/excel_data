<?php

namespace App\Imports;

use App\Helpers;
use App\Models\AccountReturnPoint;
use App\Models\BaiduData;
use App\Models\BaiduSpend;
use App\Models\ProjectType;
use App\Models\SpendData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BaiduSpendImport implements ToCollection
{
    public $count = 0;

    /**
     * BaiduSpendImport constructor.
     */
    public function __construct()
    {
    }


    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, BaiduSpend::$excelFields);

        collect($data)->filter(function ($item) {
            return isset($item['date'])
                && isset($item['promotion_plan_id']);
        })->each(function ($item) {
            $departmentType = Helpers::checkDepartment($item['promotion_plan']);
            if (!$departmentType)
                throw new \Exception('无法判断科室:' . $item['promotion_plan'] . '。请手动删除或者修改为可识别的科室.');

            $projectType = Helpers::checkDepartmentProject($departmentType, $item['promotion_plan'], 'spend_keyword');

            if (is_numeric($item['date'])) {
                $item['date'] = Date::excelToDateTimeObject($item['date']);
            }
            $item['date']       = Carbon::parse($item['date'])->toDateString();
            $item['spend_type'] = 1;
            $item['account_id'] = Helpers::formDataCheckAccount($item, 'promotion_plan', 'spend_type');
            $item['type']       = $departmentType->type;

            $baidu = BaiduSpend::updateOrCreate([
                'date'              => $item['date'],
                'promotion_plan_id' => $item['promotion_plan_id'],
            ], $item);

            $spend = SpendData::updateOrCreate([
                'model_id'   => $baidu->id,
                'model_type' => BaiduSpend::class
            ], [
                'department_id' => $departmentType->id,
                'date'          => $item['date'],
                'spend_name'    => $item['promotion_plan'],
                'show'          => $item['show'],
                'click'         => $item['click'],
                'spend'         => $item['spend'],
                'spend_type'    => $item['spend_type'],
                'account_id'    => $item['account_id'],
            ]);

            $spend->projects()->sync($projectType);
            $this->count++;
        });

    }
}
