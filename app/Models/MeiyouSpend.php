<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

class MeiyouSpend extends Model
{
    protected $fillable = [
        'type',
        'code',
        'department_id',

        'date',
        'advertiser_name',
        'show',
        'click',
        'click_rate',
        'translate',
        'spend',
        'an_start_download',
        'an_complete_download',
        'an_install',
        'an_start_download_cost',
        'an_complete_download_cost',
        'an_install_cost',
        'an_start_download_rate',
        'an_complete_download_rate',
        'an_install_rate',
    ];

    public static $fields = [
        "日期"       => 'date',
        "计划名称"     => 'advertiser_name',
        "展现量"      => 'show',
        "点击量"      => 'click',
        "点击率"      => 'click_rate',
        "转化量"      => 'translate',
        "花费"       => 'spend',
        "安卓开始下载量"  => 'an_start_download',
        "安卓下载完成量"  => 'an_complete_download',
        "安卓安装完成量"  => 'an_install',
        "安卓开始下载成本" => 'an_start_download_cost',
        "安卓下载完成成本" => 'an_complete_download_cost',
        "安卓安装完成成本" => 'an_install_cost',
        "安卓开始下载率"  => 'an_start_download_rate',
        "安卓下载完成率"  => 'an_complete_download_rate',
        "安卓安装完成率"  => 'an_install_rate',
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
        return FormData::isModel($data, static::$fields);
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

        $item['spend_type'] = FormData::$FORM_TYPE_MEIYOU;
        $item['type']       = $departmentType->type;;
        $item['department_id']   = $departmentType->id;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code, 'spend_keyword');

        return $item;
    }
}
