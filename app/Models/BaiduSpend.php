<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * @method static BaiduSpend updateOrCreate(array $array, $item)
 */
class BaiduSpend extends Model
{
    public static $excelFields = [
        '日期'     => 'date',
        '账户'     => 'account_name',
        '推广计划'   => 'promotion_plan',
        '推广计划ID' => 'promotion_plan_id',
        '展现'     => 'show',
        '点击'     => 'click',
        '消费'     => 'spend',
        "点击率"    => 'click_rate',
        "平均点击价格" => 'click_spend',
        "千次展现消费" => 'show_spend',
    ];

    protected $fillable = [
        'date',
        'account_name',
        'promotion_plan',
        'promotion_plan_id',
        'show',
        'click',
        'spend',
        'type',
        'spend_type',
        'code',
    ];

    /**
     * @param Collection $data
     * @return bool
     */
    public static function isModel($data)
    {
        $keys = array_keys(static::$excelFields);

        $first = $data->get(0);

        if ($first[2] === null && preg_match('/数据生成时间/', $first[0])) {
            return true;
        }
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
        $collection = $collection->filter(function ($item) {
            return isset($item[1])
                && isset($item[2])
                && $item[2];
        });
        $data       = Helpers::excelToKeyArray($collection, static::$excelFields);

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

            $baidu = BaiduSpend::updateOrCreate([
                'date'              => $item['date'],
                'promotion_plan_id' => $item['promotion_plan_id'],
            ], $item);

            $spend = SpendData::updateOrCreate([
                'model_id'   => $baidu->id,
                'model_type' => static::class
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
        $code = $item['code'] = $item['account_name'] . '-' . $item['promotion_plan'];
        if (is_numeric($item['date'])) {
            $item['date'] = Date::excelToDateTimeObject($item['date']);
        }
        $item['date']       = Carbon::parse($item['date'])->toDateString();
        $item['spend_type'] = 1;

        if (!$departmentType = Helpers::checkDepartment($code))
            throw new \Exception('无法判断科室:' . $code . '。请手动删除或者修改为可识别的科室.');

        $item['department_id']   = $departmentType->id;
        $item['type']            = $departmentType->type;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code, 'spend_keyword');

        return $item;
    }
}
