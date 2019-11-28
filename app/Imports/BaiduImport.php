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


    public function saveForm($item, $channel)
    {
        $clue = $this->parseClue($item['clue']);

        if ($clue->isEmpty()) return;

        $name           = 'code';
        $departmentType = Helpers::checkDepartment($item[$name]);
        if (!$departmentType) {
            $name           = 'visitor_type';
            $departmentType = Helpers::checkDepartment($item[$name]);
        }

        if (!$departmentType) {
            Log::info('无法判断科室', [
                'name' => $item['visitor_type'],
            ]);
            throw new \Exception('无法判断科室: "' . $item['visitor_type'] . '" ' . $item['code']);
        }

        $projectType       = Helpers::checkDepartmentProject($departmentType, $item[$name]);
        $type              = $departmentType->type;
        $item['form_type'] = $channel;
        $item['type']      = $type;


        $baidu = BaiduData::updateOrCreate([
            'visitor_id' => $item['visitor_id']
        ], $item);

        $form = FormData::updateOrCreate([
            'model_id'   => $baidu->id,
            'model_type' => BaiduData::class,
        ], [
            'data_type'       => $item['visitor_type'],
            'form_type'       => $channel,
            'type'            => $item['type'],
            'department_id'   => $departmentType->id,
            'date'            => $item['cur_access_time'],
            'account_id'      => Helpers::formDataCheckAccount($item, $name),
            'account_keyword' => $item[$name],
        ]);

        FormDataPhone::createOrUpdateItem($form, $clue);

        $form->projects()->sync($projectType);
        $this->count++;
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
                && isset($item['visitor_id']);
        })->each(function ($item) {
            $item['url']             = substr($item['url'] ?? '', 0, Builder::$defaultStringLength);
            $item['first_url']       = substr($item['first_url'] ?? '', 0, Builder::$defaultStringLength);
            $item['dialog_url']      = substr($item['dialog_url'] ?? '', 0, Builder::$defaultStringLength);
            $item['cur_access_time'] = Carbon::parse($item['cur_access_time'])->toDateString();

            $url = urldecode($item['first_url']);
            preg_match("/A[0-9]0(.{12,20})/", $url, $match);
            $item['code'] = $code = isset($match[0]) ? $match[0] : null;

            if ($code) {
                $channel = BaiduData::checkCodeIs($code);
                if ($channel == 1) {
                    $this->saveForm($item, $channel);
                } elseif (in_array($channel, [7, 6, 5])) {
                    $this->saveChat();
                }
            } else {
                Log::info('无法识别的跟踪码', [
                    'name' => $item['visitor_name'],
                    'url'  => $url
                ]);
            }


        });

    }

    public function saveChat()
    {

    }
}
