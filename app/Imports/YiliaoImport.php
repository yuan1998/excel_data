<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduData;
use App\Models\FormData;
use App\Models\FormDataPhone;
use App\Models\YiliaoData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class YiliaoImport implements ToCollection
{
    public $count = 0;

    public static function parserFormData($item)
    {
        return [
            'data_type'       => $item['code'],
            'form_type'       => 1,
            'type'            => $item['type'],
            'department_id'   => $item['department_id'],
            'date'            => $item['startChatTime'],
            'account_id'      => Helpers::formDataCheckAccount($item, 'code'),
            'account_keyword' => $item['code'],
        ];
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $collection = $collection->filter(function ($item) {
            return isset($item[2]) && $item[2];
        });
        $data       = Helpers::excelToKeyArray($collection, YiliaoData::$excelFields);
        foreach ($data as $item) {
            $parserItem = YiliaoData::parserData($item);
            if ($parserItem['form_type'] != 1) continue;
            if (!$parserItem['form_type'] || !$departmentType = Helpers::checkDepartment($parserItem['code'])) {
                Log::info('无法判断科室', [
                    'code' => $parserItem['code'],
                ]);
                throw new \Exception('无法判断科室: ' . $parserItem['code']);
            }
            $parserItem['type']          = $departmentType->type;
            $parserItem['department_id'] = $departmentType->id;
            $projectType                 = Helpers::checkDepartmentProject($departmentType, $item['code']);

            $yiliao = YiliaoData::updateOrCreate(['chatId' => $parserItem['chatId']], $parserItem);
            $yiliao->projects()->sync($projectType);


            if ($yiliao['phone'] && $parserItem['form_type'] == 1) {
                $form  = FormData::updateOrCreate(
                    [
                        'model_id'   => $yiliao->id,
                        'model_type' => YiliaoData::class,
                    ], static::parserFormData($parserItem));
                $phone = collect(explode(',', $yiliao['phone']));
                FormDataPhone::createOrUpdateItem($form, $phone);
                $form->projects()->sync($projectType);
            }
            $this->count++;
        }
    }
}
