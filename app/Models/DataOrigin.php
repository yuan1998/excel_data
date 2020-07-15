<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class DataOrigin extends Model
{

    public static $typeList = [
        'spend_type' => '消费数据',
        'form_type'  => '表单数据',
    ];

    public static $typePropertyField = [
        'spend_type' => [
            'code'       => '',
            'date'       => '',
            'spend'      => '',
            'show'       => '',
            'click'      => '',
            'spend_name' => '',
            'uuid'       => '',
        ],
        'form_type'  => [
            'consultant_code' => '',
            'code'            => '',
            'data_type'       => '',
            'date'            => '',
            'phone'           => '',
        ],
    ];

    public static $requireTypeProperty = [
        'spend_type' => [
            'code',
            'date',
            'spend',
            'show',
            'click',
            'spend_name',
            'uuid',
        ],
        'form_type'  => [
            'code',
            'data_type',
            'date',
            'phone',
        ]
    ];

    public $fillable = [
        'title',
        'sheet_name',
        'file_name',
        'data_type',
        'property_field',
        'data_field',
        'excel_snap',
    ];

    protected $casts = [
        'property_field' => 'json',
        'data_field'     => 'json',
        'excel_snap'     => 'json',
    ];

    public static $fieldText = [
        'consultant_code' => '咨询字段',
        'code'            => '主要标识',
        'data_type'       => '表单名称',
        'spend_name'      => '消费名称',
        'click'           => '点击量',
        'show'            => '展现量',
        'spend'           => '消耗',
        'phone'           => '电话',
        'date'            => '时间',
        'uuid'            => '唯一标识',
    ];

    public $timestamps = false;

    public $importFailLog = [
        'code_invalid' => 0,
        'code_log'     => []
    ];

    public $importSuccessLog = [
        'code_log' => []
    ];

    public static $_dataOrigin;


    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'data_origin_has_channel', 'data_origin_id', 'channel_id');
    }


    /**
     * @return Collection
     */
    public static function getDataOrigin()
    {
        if (!static::$_dataOrigin) {
            static::$_dataOrigin = static::query()
                ->with(['channels'])
                ->select([
                    'id', 'data_type', 'property_field', 'data_field', 'sheet_name', 'title'
                ])
                ->get()->map(function ($item) {
                    $headers = collect();

                    foreach ($item['property_field'] as $value) {
                        $headers = $headers->merge($value);
                    }
                    $item['headers'] = $headers->merge($item['data_field'])->unique();
                    return $item;
                });
        }
        return static::$_dataOrigin;
    }


    /**
     * @param $sheetName string
     * @param $headers   array
     * @return bool|DataOrigin
     */
    public static function validateExcelType($sheetName, $headers)
    {
        $dataOrigin = static::getDataOrigin();

//        $dataOrigin = $dataOrigin->filter(function ($item) use ($sheetName) {
//            return $item['sheet_name'] === $sheetName;
//        });
//
//        if (!$dataOrigin->count())
//            return false;


        return $dataOrigin->first(function ($item) use ($headers) {
            $diff = $item['headers']->diff($headers);
            return $diff->count() === 0;
        });

    }


    public function checkChannel($code)
    {

        if (!$this->channels) return false;
//            throw new \Exception("数据源 ({$this->title}) 未关联渠道!");

        if ($this->channels->count() === 1)
            return $this->channels->first();


        return $this->channels->first(function ($channel) use ($code) {
            if (!$channel->keyword) {
                return false;
            }
            $keywords = preg_replace('/(\,)/', '|', $channel->keyword);
            return !!preg_match("/{$keywords}/", $code);
        });
    }

    public function parserPropertyField($data)
    {
        $property = $this->property_field;
        $item     = [];

        foreach ($property as $key => $fieldArray) {
            $item[$key] = collect($fieldArray)
                ->map(function ($field) use ($data) {
                    $value = Arr::get($data, $field, '');

                    if ($value && $query = Helpers::isUrl($value)) {
                        $value = $query;
                    }

                    return $value;
                })
                ->join('_');
        }
        return $item;
    }

    public function validateSpendRequiredField($propertyData, $codeValue)
    {
        foreach (['uuid', 'spend', 'click', 'show'] as $item) {
            $value = $propertyData[$item];
            if ($value === '' || $value === null) {
                $text                                        = static::$fieldText[$item];
                $this->importFailLog['code_log'][$codeValue] = "消费数据的{$text}不存在: {$value}";
                return false;
            }
        }
        return true;
    }

    public function dataTypeFilter($data)
    {
        $arr = collect();

        foreach ($data as $raw) {
            $propertyData = $this->parserPropertyField($raw);
            $dateValue    = $propertyData['date'];
            $codeValue    = $propertyData['code'];

            if (!$codeValue) {
                $this->importFailLog['code_invalid']++;
                continue;
            }

            if (!Helpers::isDate($dateValue)) {
                $this->importFailLog['code_log'][$codeValue] = "无法判断该条数据的时间格式: {$dateValue}";
                continue;
            }

            if (!$channel = $this->checkChannel($codeValue)) {
                $this->importFailLog['code_log'][$codeValue] = "无法判断该条数据的所属渠道: {$codeValue}";
                continue;
            }

            if (!$departmentType = Helpers::checkDepartment($codeValue)) {
                $this->importFailLog['code_log'][$codeValue] = "无法判断该条数据的所属科室: {$codeValue}";
                continue;
            }


            $propertyData['date']            = Carbon::parse($propertyData['date'])->toDateString();
            $propertyData['type']            = $departmentType->type;
            $propertyData['department_type'] = $departmentType;
            $propertyData['department_id']   = $departmentType->id;
            $account_type                    =
            $propertyData['account_type'] = $channel->checkAccount($propertyData['type'], $codeValue, true);


            if ($account_type)
                $propertyData['account_id'] = $account_type['id'];


            if ($this->data_type === 'spend_type') {
                if (!$this->validateSpendRequiredField($propertyData, $codeValue)) continue;

                if (!Helpers::validateFormat($dateValue, 'Y-m-d')) {
                    $this->importFailLog['code_log'][$codeValue] = "消费数据必须为每日消费: {$dateValue}";
                    continue;
                }

                $off_spend = (float)$propertyData['spend'];
                if ($account_type) {
                    $off_spend = $off_spend / (float)$account_type['rebate'];
                }
                $propertyData['off_spend'] = $off_spend;

            }

            if ($this->data_type === 'form_type') {
                $phoneValue = $propertyData['phone'];
                if (!$phone = Helpers::validatePhone($phoneValue, true)) {
                    $this->importFailLog['code_log'][$codeValue] = "表单数据的电话格式错误或者为空: {$phoneValue}";
                    continue;
                }
                $propertyData['phone'] = $phone;
                if ($propertyData['consultant_code']) {
                    $propertyData['consultant_id'] = Helpers::checkConsultantNameOf($propertyData['type'], $propertyData['consultant_code']);
                }
            }


            $propertyData['project_type'] = Helpers::checkDepartmentProject($departmentType, $codeValue);
            $propertyData['channel_type'] = $channel;
            $propertyData['channel_id']   = $channel->id;
            $propertyData['data_snap']    = json_encode($raw);
            $arr->push($propertyData);

        }
        return $arr;
    }

    public function dataOriginMakeFormData($data)
    {
        foreach ($data as $item) {
            $lockKey = "CHANNEL_{$item['channel_id']}_DATE_{$item['date']}_LOCK_{$item['phone']}";
            if (Redis::exists($lockKey))
                continue;
            Redis::setex($lockKey, 20, 1);

            $makeData = Arr::only($item, ['code', 'phone', 'data_type', 'consultant_code', 'date', 'type', 'department_id', 'account_id', 'consultant_id', 'channel_id', 'data_snap']);

            $form = FormData::updateOrCreate([
                'channel_id' => $item['channel_id'],
                'date'       => $item['date'],
                'phone'      => $item['phone'],
            ], $makeData);

            $phone = BaiduData::parseClue($item['phone']);
            FormDataPhone::createOrUpdateItem($form, $phone);
            $form->projects()->sync($item['project_type']);
            $this->importSuccessLog['code_log'][] = $item['code'];
        }
    }

    public function dataOriginMakeSpendData($data)
    {
        foreach ($data as $item) {
            $lockKey = "CHANNEL_{$item['channel_id']}_DATE_{$item['date']}_LOCK_{$item['uuid']}";
            if (Redis::exists($lockKey))
                continue;
            Redis::setex($lockKey, 20, 1);

            $makeData = Arr::only($item, [
                'code', 'uuid', 'spend', 'off_spend', 'date', 'type', 'department_id', 'account_id', 'spend_name', 'channel_id', 'data_snap', 'show', 'click'
            ]);

            SpendData::updateOrCreate([
                'channel_id' => $item['channel_id'],
                'date'       => $item['date'],
                'uuid'       => $item['uuid'],
            ], $makeData);
            $this->importSuccessLog['code_log'][] = $item['code'];
        }

    }

    public function makeFormData($collection)
    {
        $data       = Helpers::excelToKeyArray($collection);
        $filterData = $this->dataTypeFilter($data);

        if ($this->data_type === 'spend_type') {
            $this->dataOriginMakeSpendData($filterData);
        } else {

            $this->dataOriginMakeFormData($filterData);
        }

    }

}
