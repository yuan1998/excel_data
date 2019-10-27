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

    public function parserFormType(string $str)
    {
        if (preg_match("/抖音/", $str)) {
            return 4;
        }
        return 3;
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
            $departmentType = Helpers::checkDepartment($item['activity_name']);

            if (!$departmentType)
                throw new \Exception('无法判断科室:' . $item['activity_name']);

            $projectType = Helpers::checkDepartmentProject($departmentType, $item['activity_name']);

            if (count($projectType) > 1) {
                throw new \Exception('识别为多个病种:' . $item['activity_name']);
            }


            $type                   = $departmentType->type;
            $item['type']           = $type;
            $item['sponsored_link'] = substr($item['sponsored_link'] ?? '', 0, Builder::$defaultStringLength);

            $item['post_date'] = Carbon::parse($item['post_date'])->toDateString();

            FeiyuData::updateOrCreate([
                'clue_id' => $item['clue_id']
            ], $item);

            $form = FormData::updateOrCreate([
                'feiyu_id' => $item['clue_id'],
            ], [
                'data_type'     => $item['activity_name'],
                'department_id' => $departmentType->id,
                'form_type'     => $this->parserFormType($item['source']),
                'date'          => $item['post_date'],
                'type'          => $item['type'],
                'project_id' => 0,
            ]);

            FormDataPhone::createOrUpdateItem($form, collect($item['phone']));

            $form->projects()->sync($projectType);
        });

    }
}
