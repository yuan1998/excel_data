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


    public static function parserData($item)
    {
        $item['code'] = $item['account_name'] . '-' . $item['promotion_plan'];
        if (is_numeric($item['date'])) {
            $item['date'] = Date::excelToDateTimeObject($item['date']);
        }
        $item['date']       = Carbon::parse($item['date'])->toDateString();
        $item['spend_type'] = 1;
        return $item;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $collection = $collection->filter(function ($item) {
            return isset($item[1])
                && isset($item[2])
                && $item[2];
        });
        $data       = Helpers::excelToKeyArray($collection, BaiduSpend::$excelFields);

        foreach ($data as $item) {
            $item = static::parserData($item);
            $code = $item['code'];

            if (!$departmentType = Helpers::checkDepartment($code))
                throw new \Exception('无法判断科室:' . $code . '。请手动删除或者修改为可识别的科室.');

            $projectType  = Helpers::checkDepartmentProject($departmentType, $code, 'spend_keyword');
            $item['type'] = $departmentType->type;

            $baidu = BaiduSpend::updateOrCreate([
                'date'              => $item['date'],
                'promotion_plan_id' => $item['promotion_plan_id'],
            ], $item);

            $account  = Helpers::formDataCheckAccount($item, 'account_name', 'spend_type', true);
            $offSpend = (float)$item['spend'];
            if ($account) {
                $offSpend = $offSpend * (float)$account['rebate'];
            }

            $spend = SpendData::updateOrCreate([
                'model_id'   => $baidu->id,
                'model_type' => BaiduSpend::class
            ], [
                'type'            => $item['type'],
                'department_id'   => $departmentType->id,
                'date'            => $item['date'],
                'spend_name'      => $code,
                'show'            => $item['show'],
                'click'           => $item['click'],
                'spend'           => $item['spend'],
                'off_spend'       => $offSpend,
                'spend_type'      => $item['spend_type'],
                'account_id'      => $account ? $account['id'] : null,
                'account_keyword' => $code,
            ]);

            $spend->projects()->sync($projectType);
            $this->count++;

        }

    }
}
