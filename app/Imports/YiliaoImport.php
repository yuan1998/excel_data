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

            $departmentType = Helpers::checkDepartment($parserItem['code']);
            if (!$departmentType) {
                Log::info('无法判断科室', [
                    'name' => $parserItem['extCard1'],
                    'code' => $parserItem['code'],
                ]);
                throw new \Exception('无法判断科室: "' . $parserItem['extCard1'] . '" ' . $parserItem['code']);
            }

            $parserItem['type']          = $departmentType->type;
            $parserItem['department_id'] = $departmentType->id;
            $projectType                 = Helpers::checkDepartmentProject($departmentType, $item['code']);


            $yiliao = YiliaoData::updateOrCreate(['chatId' => $parserItem['chatId']], $parserItem);
            $yiliao->projects()->sync($projectType);
            if ($yiliao['phone']) {
                $form  = FormData::updateOrCreate(
                    [
                        'model_id'   => $yiliao->id,
                        'model_type' => YiliaoData::class,
                    ], [
                    'data_type'       => $yiliao['code'],
                    'form_type'       => 1,
                    'type'            => $yiliao['type'],
                    'department_id'   => $departmentType->id,
                    'date'            => $yiliao['startChatTime'],
                    'account_id'      => Helpers::formDataCheckAccount($yiliao, 'code'),
                    'account_keyword' => $item['code'],
                ]);
                $phone = collect(explode(',', $yiliao['phone']));
                FormDataPhone::createOrUpdateItem($form, $phone);
                $form->projects()->sync($projectType);
            }
            $this->count++;
        }
    }
}
