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
            $name           = $item['advertiser_account'];
            $departmentType = Helpers::checkDepartment($name);
            if (!$departmentType)
                throw new \Exception('无法判断科室:' . $name);

            $projectType = Helpers::checkDepartmentProject($departmentType, $name, 'spend_keyword');

            $item['spend_type'] = 2;
            $item['type']       = $departmentType->type;
            $interactive        = (int)Arr::get($item, 'comment_count', 0)
                + (int)Arr::get($item, 'start_count', 0)
                + (int)Arr::get($item, 'share_count', 0);

            $weibo = WeiboSpend::updateOrCreate([
                'date'               => $item['date'],
                'advertiser_account' => $name,
            ], $item);


            $account  = Helpers::formDataCheckAccount($item, 'advertiser_account', 'spend_type', true);
            $offSpend = (float)$item['spend'];
            if ($account) {
                $offSpend = $offSpend * (float)$account['rebate'];
            }

            $spend = SpendData::updateOrCreate([
                'model_id'   => $weibo->id,
                'model_type' => WeiboSpend::class
            ], [
                'type'            => $item['type'],
                'department_id'   => $departmentType->id,
                'date'            => $item['date'],
                'click'           => $item['interactive'],
                'spend_name'      => $name,
                'show'            => $item['show'],
                'off_spend'       => $offSpend,
                'spend'           => $item['spend'],
                'interactive'     => $interactive,
                'spend_type'      => $item['spend_type'],
                'account_id'      => $account ? $account['id'] : null,
                'account_keyword' => $name,
            ]);

            $spend->projects()->sync($projectType);
            $this->count++;
        });


    }
}
