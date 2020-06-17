<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

class KuaiShouSpend extends Model
{
    public static $fields = [
        "日期"         => 'date',
        "广告计划"       => 'advertiser_name',
        "花费"         => 'spend',
        "封面曝光数"      => 'cover_show',
        "封面点击数"      => 'click',
        "素材曝光数"      => 'material_show',
        "行为数"        => 'behavior',
        "封面点击率"      => 'cover_click_rate',
        "行为率"        => 'behavior_rate',
        "平均千次封面曝光花费" => 'svg_cover_show_cost',
        "平均封面点击单价"   => 'svg_cover_click_cost',
        "平均行为单价"     => 'svg_behavior_cost',
    ];

    protected $fillable = [
        'type',
        'code',
        'department_id',

        'date',
        'advertiser_name',
        'spend',
        'cover_show',
        'click',
        'material_show',
        'behavior',
        'cover_click_rate',
        'behavior_rate',
        'svg_cover_show_cost',
        'svg_cover_click_cost',
        'svg_behavior_cost',
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

    public static function isModel($data)
    {
        $keys  = array_keys(static::$fields);
        $first = $data->get(0);
        $diff  = $first->diff($keys);

        $count = $diff->count();
        return $count <= 1;
    }


    public static function excelCollection($collection)
    {
        $data = Helpers::excelToKeyArray($collection, static::$fields);

        $data = collect($data)->filter(function ($item) {
            $date = Arr::get($item, 'date', null);
            return Helpers::validateFormat($date, 'Y-m-d');
        });


        return static::handleExcelData($data);
    }

    public static function handleExcelData($data)
    {
        $count = 0;

        foreach ($data as $item) {
            $item = static::parserItem($item);
            SpendData::baseMakeModelData(static::class, $item);
            $count++;
        }

        return $count;
    }

    public static function parserItem($item)
    {
        $code = $item['code'] = $item['advertiser_name'];
        if (!$departmentType = Helpers::checkDepartment($code)) {
            throw new \Exception('无法判断科室:' . $code);
        }
        $item['show'] = $item['cover_show'];
        $item['spend_type'] = FormData::$FORM_TYPE_KUAISHOU;
        $item['type']       = $departmentType->type;;
        $item['department_id']   = $departmentType->id;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code, 'spend_keyword');

        return $item;
    }

}
