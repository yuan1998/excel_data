<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class MeiyouData extends Model
{

    protected $fillable = [
        'question_data',
        'type',
        'form_type',
        'code',
        'date',
        'department_id',

        'site_id',
        'advertiser_name',
        'material_name',
        'origin',
        'name',
        'phone',
        'post_date',
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


    public static $fields = [
        "站点ID"  => 'site_id',
        "计划名称"  => 'advertiser_name',
        "素材组名称" => 'material_name',
        "来源"    => 'origin',
        "姓名"    => 'name',
        "手机号码"  => 'phone',
        "创建时间"  => 'post_date',
    ];

    protected $casts = [
        'question_data' => 'json'
    ];

    /**
     * @param $data
     * @return mixed
     */
    public static function isModel($data)
    {
        $keys = $data->get(0);

        return $keys->contains('站点ID')
            && $keys->contains('素材组名称')
            && $keys->contains('计划名称')
            && $keys->contains('来源')
            && $keys->contains('姓名')
            && $keys->contains('手机号码')
            && $keys->contains('创建时间');
    }

    /**
     * @param $collection
     * @return int
     * @throws \Exception
     */
    public static function excelCollection($collection)
    {
        $data = Helpers::excelToKeyArray($collection, static::$fields);
        $data = collect($data)->filter(function ($item) {
            return isset($item['post_date'])
                && isset($item['advertiser_name'])
                && isset($item['phone']);
        });

        return static::handleExcelData($data);
    }

    public static function handleExcelData($data)
    {
        $count = 0;
        foreach ($data as $item) {
            $item = static::parseData($item);
            FormData::baseMakeFormData(static::class, $item, [
                'date'  => $item['date'],
                'phone' => $item['phone'],
            ]);
            $count++;
        }
        return $count;
    }

    public static function parseData($item)
    {
        $keys     = array_values(static::$fields);
        $question = collect($item)->filter(function ($item, $key) use ($keys) {
            return !in_array($key, $keys);
        });
        $code     = $item['advertiser_name'] . '-' . $question->join('-');

        if (!$departmentType = Helpers::checkDepartment($code))
            throw new \Exception('无法判断科室 :' . $code);

        $item['date']            = Carbon::parse($item['post_date'])->toDateString();
        $item['department_id']   = $departmentType->id;
        $item['type']            = $departmentType->type;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code);
        $item['form_type']       = FormData::$FORM_TYPE_MEIYOU;
        $item['question_data']   = $question;
        $item['code']            = $code;
        return $item;
    }

}
