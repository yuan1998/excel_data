<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method static updateOrCreate(array $array, $item)
 */
class FeiyuSpend extends Model
{
    public static $excelFields = [
        "时间"          => 'date',
        "广告组id"       => 'advertiser_id',
        "广告组"         => 'advertiser_name',
        "展示数"         => 'show',
        "点击数"         => 'click',
        "点击率(%)"      => 'click_rate',
        "平均点击单价(元)"   => 'average_click_price',
        "平均千次展现费用(元)" => 'average_thousand_times_show_price',
        "消耗"          => 'spend',
        "转化数"         => 'conversion',
        "转化成本"        => 'conversion_price',
        "转化率"         => 'conversion_rate',
        "深度转化次数"      => 'deep_conversion',
        "深度转化成本"      => 'deep_conversion_price',
        "深度转化率"       => 'deep_conversion_rate',
    ];

    public static $requireFields = [
        'spend'           => ['总花费(元)', '消耗'],
        'advertiser_id'   => ['广告组id'],
        'advertiser_name' => ['广告组'],
        'show'            => ["展示数"],
        'click'           => ["点击数"],
    ];


    protected $fillable = [
        'type',
        'date',
        'advertiser_id',
        'advertiser_name',
        'show',
        'click',
        'click_rate',
        'average_click_price',
        'average_thousand_times_show_price',
        'spend',
        'conversion',
        'conversion_price',
        'conversion_rate',
        'deep_conversion',
        'deep_conversion_price',
        'deep_conversion_rate',
    ];

    /**
     * @param Collection $data
     * @return bool
     */
    public static function isModel($data)
    {
        $keys = array_keys(static::$excelFields);

        $first = $data->get(0);
        $diff  = $first->diff($keys);

        $count = $diff->count();
        return $count <= 2;
    }

    /**
     * @param Collection $collection
     * @return int
     * @throws \Exception
     */
    public static function excelCollection($collection)
    {
        $data = Helpers::excelToKeyArray($collection, static::$excelFields);

        $data = collect($data)->filter(function ($item) {
            return isset($item['date'])
                && isset($item['advertiser_id'])
                && isset($item['advertiser_name']);
        });

        return static::handleExcelData($data);
    }

    /**
     * @param $data
     * @return int
     * @throws \Exception
     */
    public static function handleExcelData($data)
    {
        $count = 0;
        foreach ($data as $item) {
            $item = static::parseData($item);

            $feiyu = FeiyuSpend::updateOrCreate([
                'date'          => $item['date'],
                'advertiser_id' => $item['advertiser_id'],
            ], $item);

            $spend = SpendData::updateOrCreate([
                'model_id'   => $feiyu->id,
                'model_type' => FeiyuSpend::class
            ], SpendData::parseMakeSpendData($item));
            $spend->projects()->sync($item['project_type']);
            $count++;
        }

        return $count;
    }

    /**
     * @param $item
     * @return mixed
     * @throws \Exception
     */
    public static function parseData($item)
    {
        $code = $item['code'] = $item['advertiser_name'];

        if (!$departmentType = Helpers::checkDepartment($code)) {
            throw new \Exception('无法判断科室:' . $code);
        }
        $item['type'] = $departmentType->type;;
        $item['department_id']   = $departmentType->id;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code, 'spend_keyword');

        $item['advertiser_id'] = trim($item['advertiser_id']);

        if (!$item['spend_type'] = static::parseCodeType($code))
            throw new \Exception('无法判断渠道' . $code);

        $item['date'] = Carbon::parse($item['date'])->toDateString();

        return $item;
    }

    public static function parseCodeType($str)
    {
        if (preg_match("/B/", $str)) {
            return 3;
        }
        if (preg_match("/D/", $str)) {
            return 4;
        }
        return 0;
    }
}
