<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduData;
use App\Models\FeiyuData;
use App\Models\FormData;
use App\Models\FormDataPhone;
use App\Models\ProjectType;
use Carbon\Carbon;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Symfony\Component\VarDumper\VarDumper;

class FeiyuImport implements ToCollection
{
    public $count = 0;

    public function parserFormType($str)
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
     * 将Excel数据写入 FeiyuData 数据库中.
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, FeiyuData::$excelFields);

        collect($data)->filter(function ($item) {
            return isset($item['post_date'])
                && isset($item['owner'])
                && isset($item['component_id'])
                && isset($item['activity_name'])
                && isset($item['phone'])
                && isset($item['sponsored_link'])
                && !!$item['activity_name'];
        })->each(function ($item) {
            $name = $item['activity_name'];

            $departmentType = Helpers::checkDepartment($name);
            if (!$departmentType)
                throw new \Exception('无法判断科室:' . $name);

            $projectType = Helpers::checkDepartmentProject($departmentType, $name);

            $item['type']           = $departmentType->type;
            $item['sponsored_link'] = substr($item['sponsored_link'] ?? '', 0, Builder::$defaultStringLength);
            $item['post_date']      = Carbon::parse($item['post_date'])->toDateString();
            $item['form_type']      = $this->parserFormType($name);
            $feiyu                  = FeiyuData::updateOrCreate([
                'clue_id' => $item['clue_id']
            ], $item);


            $form = FormData::updateOrCreate([
                'model_id'   => $feiyu->id,
                'model_type' => FeiyuData::class,
            ], [
                'data_type'       => $name,
                'department_id'   => $departmentType->id,
                'form_type'       => $item['form_type'],
                'date'            => $item['post_date'],
                'type'            => $item['type'],
                'account_id'      => Helpers::formDataCheckAccount($item, 'activity_name'),
                'account_keyword' => $name,
            ]);

            FormDataPhone::createOrUpdateItem($form, collect($item['phone']));

            $form->projects()->sync($projectType);
            $this->count++;
        });

    }
}
