<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * @method static VivoData updateOrCreate(array $array, $item)
 */
class VivoData extends Model
{

    public static $excelFields = [
        "站点ID" => 'site_id',
        "站点名称" => 'site_name',
        "姓名"   => 'name',
        "电话"   => 'phone',
        "提交时间" => 'post_date',
        "线索ID" => 'clue_id',
    ];

    protected $fillable = [
        'site_id',
        'site_name',
        'name',
        'phone',
        'post_date',
        'clue_id',

        // custom Field
        'form_type',
        'date',
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
            && (
            (
                $first->contains('站点ID')
                && $first->contains('线索ID')
                && $first->contains('电话')
                && $first->contains('站点名称')
                && $first->contains('线索数据')
                && $first->contains('提交时间')
            )
            );
    }


    /**
     * @param Collection $collection
     * @return int
     * @throws \Exception
     */
    public static function excelCollection($collection)
    {

        $collection = $collection->filter(function ($item) {
            return isset($item[2]) && $item[2];
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
            $item = static::parserData($item);

            FormData::baseMakeFormData(static::class, $item, [
                'date'  => $item['date'],
                'phone' => $item['phone'],
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * @param $item
     * @return mixed
     * @throws \Exception
     */
    public static function parserData($item)
    {
        $item['form_type'] = FormData::$FORM_TYPE_VIVO;
        $item['date']      = Carbon::parse($item['post_date'])->toDateString();
        $item['code']      = $item['site_name'];
        $code              = $item['code'];

        if (!$departmentType = Helpers::checkDepartment($code)) {
            Log::info('无法判断科室', [
                'code' => $code,
            ]);
            throw new \Exception('无法判断科室: ' . $code);
        }
        $item['department_id']   = $departmentType->id;
        $item['type']            = $departmentType->type;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code);

        return $item;
    }

}
