<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

class KuaiShouData extends Model
{

    protected $fillable = [
        'type',
        'form_type',
        'code',
        'date',
        'department_id',

        'clue_id',
        'name',
        'phone',
        'clue_status',
        'call_status',
        'follow_status',
        'tags',
        'post_date',
        'page_name',
        'form_component',
        'comment',
        'description',
    ];

    public static $fields = [
        "线索id" => 'clue_id',
        "姓名"   => 'name',
        "电话"   => 'phone',
        "线索状态" => 'clue_status',
        "通话状态" => 'call_status',
        "跟进状态" => 'follow_status',
        "标签"   => 'tags',
        "收集时间" => 'post_date',
        "落地页"  => 'page_name',
        "表单"   => 'form_component',
        "备注"   => 'comment',
        "详情"   => 'description',
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

        $diff = $first->diff($keys);

        $count = $diff->count();
        return $count <= 1;
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
                && isset($item['clue_id'])
                && isset($item['page_name'])
                && isset($item['phone']);
        });

        return static::handleExcelData($data);
    }

    /**
     * @param $data Collection
     * @return int
     * @throws \Exception
     */
    public static function handleExcelData($data): int
    {
        $count = 0;
        foreach ($data as $item) {
            $item  = static::parseData($item);
            $model = static::updateOrCreate([
                'date'  => $item['date'],
                'phone' => $item['phone'],
            ], $item);
            $model->projects()->sync($item['project_type']);

            $phone = $item['phone'];
            if ($phone) {
                $form  = FormData::updateOrCreate(
                    [
                        'model_id'   => $model->id,
                        'model_type' => static::class,
                    ], FormData::parseFormData($item));
                $phone = collect(explode(',', $phone));
                FormDataPhone::createOrUpdateItem($form, $phone);
                $form->projects()->sync($item['project_type']);
                $count++;
            }
        }

        return $count;
    }


    /**
     * @param $item array
     * @return array
     * @throws \Exception
     */
    public static function parseData($item): array
    {
        $item['form_type'] = FormData::$FORM_TYPE_KUAISHOU;
        $code              = $item['code'] = $item['page_name'] . '-' . $item['form_component'];

        if (!$departmentType = Helpers::checkDepartment($code))
            throw new \Exception('无法判断科室 :' . $code);

        $item['date']            = Carbon::parse($item['post_date'])->toDateString();
        $item['department_id']   = $departmentType->id;
        $item['type']            = $departmentType->type;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code);

        return $item;
    }

}
