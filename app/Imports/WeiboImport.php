<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduData;
use App\Models\FormData;
use App\Models\FormDataPhone;
use App\Models\ProjectType;
use App\Models\WeiboData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class WeiboImport implements ToCollection
{
    public $count = 0;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, WeiboData::$excelFields);

        collect($data)->filter(function ($item) {
            return isset($item['weibo_id'])
                && isset($item['phone'])
                && !!$item['phone']
                && isset($item['post_date']);

        })->each(function ($item) {
            $departmentType = Helpers::checkDepartment($item['project_name']);

            if (!$departmentType)
                throw new \Exception('无法判断科室: ' . $item['project_name']);

            $projectType = Helpers::checkDepartmentProject($departmentType, $item['project_name']);

            $type         = $departmentType->type;
            $item['type'] = $type;
            $item['post_date'] = Carbon::parse($item['post_date'])->toDateString();

            $weibo = WeiboData::updateOrCreate([
                'weibo_id' => $item['weibo_id']
            ], $item);

            $form = FormData::updateOrCreate([
                'model_id'   => $weibo->id,
                'model_type' => WeiboData::class,
            ], [
                'data_type'     => $item['project_name'],
                'form_type'     => 2,
                'department_id' => $departmentType->id,
                'type'          => $item['type'],
                'date'          => $item['post_date'],
            ]);
            FormDataPhone::createOrUpdateItem($form, collect($item['phone']));

            $form->projects()->sync($projectType);
            $this->count++;
        });

    }
}
