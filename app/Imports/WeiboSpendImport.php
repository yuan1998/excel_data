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
    public $count = 0;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, WeiboSpend::$excelFields);

        collect($data)->filter(function ($item) {
            return isset($item['date'])
                && isset($item['advertiser_account'])
                && isset($item['diversions'])
                && isset($item['follow_count'])
                && $item['advertiser_account'] != '-';
        })->each(function ($item) {
            $departmentType = Helpers::checkDepartment($item['advertiser_account']);
            if (!$departmentType)
                throw new \Exception('无法判断科室:' . $item['advertiser_account']);

            $projectType = Helpers::checkDepartmentProject($departmentType, $item['advertiser_account'], 'spend_keyword');

            $item['spend_type'] = 2;
            $item['type']       = $departmentType->type;
            $item['account_id'] = Helpers::formDataCheckAccount($item, 'advertiser_account', 'spend_type');
            $interactive        = (int)Arr::get($item, 'comment_count', 0)
                + (int)Arr::get($item, 'start_count', 0)
                + (int)Arr::get($item, 'share_count', 0);

            $weibo = WeiboSpend::updateOrCreate([
                'date'               => $item['date'],
                'advertiser_account' => $item['advertiser_account'],
            ], $item);

            $spend = SpendData::updateOrCreate([
                'model_id'   => $weibo->id,
                'model_type' => WeiboSpend::class
            ], [
                'department_id' => $departmentType->id,
                'date'          => $item['date'],
                'click'         => $item['interactive'],
                'spend_name'    => $item['advertiser_account'],
                'show'          => $item['show'],
                'spend'         => $item['spend'],
                'interactive'   => $interactive,
                'spend_type'    => $item['spend_type'],
                'account_id'    => $item['account_id'],
            ]);

            $spend->projects()->sync($projectType);
            $this->count++;
        });


    }
}
