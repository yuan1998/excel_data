<?php

namespace App\Imports;

use App\Helpers;
use App\Models\BaiduClue;
use App\Models\BaiduData;
use App\Models\FormData;
use App\Models\FormDataPhone;
use App\Models\ProjectType;
use Carbon\Carbon;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class BaiduImport implements ToCollection
{

    public $count = 0;

    /**
     * 拆解和过滤 Clues.
     * @param string $clue
     * @return Collection
     */
    public function parseClue(string $clue)
    {
        return collect(explode(',', $clue))
            ->filter(function ($value) {
                return Helpers::validatePhone($value);
            })
            ->map(function ($value) {
                return $value;
            });
    }

    public function checkDepartment($key)
    {
        $departmentType = Helpers::checkDepartment($key);
        if (!$departmentType) {
            Log::info('无法判断科室', [
                'name' => $key,
            ]);
            throw new \Exception('无法判断科室: ' . $key);
        }

        return $departmentType;
    }

    public function parserData($item)
    {
        $item['url']             = substr($item['url'] ?? '', 0, Builder::$defaultStringLength);
        $item['first_url']       = substr($item['first_url'] ?? '', 0, Builder::$defaultStringLength);
        $item['dialog_url']      = substr($item['dialog_url'] ?? '', 0, Builder::$defaultStringLength);
        $item['cur_access_time'] = Carbon::parse($item['cur_access_time'])->toDateString();
        $url                     = urldecode($item['dialog_url']);
        preg_match("/\?A[0-9](.{12,20})/", $url, $match);
        $item['code']      = (isset($match[0]) ? $match[0] : '') . '-' . $item['visitor_type'];
        $item['form_type'] = BaiduData::checkCodeIs($item['code']);

        return $item;
    }

    public static function parseFormData($item)
    {
        return [
            'data_type'       => $item['code'],
            'form_type'       => $item['form_type'],
            'type'            => $item['type'],
            'department_id'   => $item['department_id'],
            'date'            => $item['cur_access_time'],
            'account_id'      => Helpers::formDataCheckAccount($item, 'code'),
            'account_keyword' => $item['code'],
        ];

    }

    /**
     * 将Excel 数据插入到BaiduData中
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, BaiduData::$excelFields);

        collect($data)->filter(function ($item) {
            return isset($item['dialog_url'])
                && isset($item['cur_access_time'])
                && isset($item['visitor_name'])
                && isset($item['visitor_id'])
                && $item['dialog_url']
                && $item['visitor_type'];
        })->each(function ($item) {
            $item = $this->parserData($item);
            if (!$item['form_type']) return;
            if (!$departmentType = Helpers::checkDepartment($item['code'])) {
                Log::info('无法判断科室', [
                    'name' => $item['code'],
                ]);
                throw new \Exception('无法判断科室: ' . $item['code']);
            }
            $item['type']          = $departmentType->type;
            $item['department_id'] = $departmentType->id;
            $projectType           = Helpers::checkDepartmentProject($departmentType, $item['code']);

            $baidu = BaiduData::updateOrCreate([
                'visitor_id' => $item['visitor_id']
            ], $item);
            $baidu->projects()->sync($projectType);


            $clue = $this->parseClue($item['clue']);
            if (in_array($baidu['form_type'], [1, 8]) && $clue->isNotEmpty()) {
                $form = FormData::updateOrCreate([
                    'model_id'   => $baidu->id,
                    'model_type' => BaiduData::class,
                ], static::parseFormData($item));
                FormDataPhone::createOrUpdateItem($form, $clue);
                $form->projects()->sync($projectType);
                $this->count++;
            }
        });
    }


}
