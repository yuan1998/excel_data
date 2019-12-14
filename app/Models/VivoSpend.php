<?php

namespace App\Models;

use App\Helpers;
use App\OppoSpend;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * @method static VivoSpend updateOrCreate(array $array, $item)
 */
class VivoSpend extends Model
{

    public static $excelFields = [
        "日期"     => 'date',
        "广告计划"   => 'ad_plan_name',
        "曝光量"    => 'show',
        "点击量"    => 'click',
        "点击率"    => 'click_rate',
        "千次展示均价" => 'thousand_click_spend',
        "点击均价"   => 'click_spend',
        "花费"     => 'spend',
        "下载量"    => 'download_count',
        "展示下载率"  => 'show_download_rate',
        "下载均价"   => 'download_spend',
        "表单提交量"  => 'form_count',
        "表单提交成本" => 'form_spend',
    ];

    protected $fillable = [
        'date',
        'ad_plan_name',
        'show',
        'click',
        'click_rate',
        'thousand_click_spend',
        'click_spend',
        'spend',
        'download_count',
        'show_download_rate',
        'download_spend',
        'form_count',
        'form_spend',

        // custom
        'type',
        'code',
        'department_id',
    ];


    /**
     * 关联 项目
     * @return MorphToMany
     */
    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    /**
     * 关联 科室
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(DepartmentType::class, 'department_id', 'id');
    }


    /**
     * @param Collection $data
     * @return bool
     */
    public static function isModel($data)
    {
        $first = $data->get(0);
        return $first
            && $first->contains('日期')
            && $first->contains('广告计划')
            && $first->contains('曝光量')
            && $first->contains('点击量')
            && $first->contains('表单提交量')
            && $first->contains('展示下载率')
            && $first->contains('下载量')
            && $first->contains('花费');
    }

    /**
     * @param Collection $collection
     * @return int
     * @throws \Exception
     */
    public static function excelCollection($collection)
    {
        $data = Helpers::excelToKeyArray($collection, static::$excelFields);

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
            $item = static::parserItem($item);

            $model = static::updateOrCreate([
                'ad_plan_name' => $item['ad_plan_name'],
                'date'         => $item['date'],
            ], $item);
            $model->projects()->sync($item['project_type']);


            $spend = SpendData::updateOrCreate([
                'model_id'   => $model->id,
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
    public static function parserItem($item)
    {
        $code               = $item['code'] = $item['ad_plan_name'];
        $item['spend_type'] = 9;


        if (!$departmentType = Helpers::checkDepartment($code)) {
            throw new \Exception('无法判断科室:' . $code);
        }
        $item['type'] = $departmentType->type;;
        $item['department_id']   = $departmentType->id;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code, 'spend_keyword');

        return $item;
    }


}
