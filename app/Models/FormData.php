<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed data_type
 * @property mixed project_info
 * @property mixed department_info
 */
class FormData extends Model
{
    protected $fillable = [
        'phone',
        'weibo_id',
        'baidu_id',
        'feiyu_id',
        'archive_type',
        'form_type',
        'date',
        'data_type',
        'department_id',
        'type',
        'project_id',
    ];

    // 平台类型列表
    public static $FormTypeList = [
        1 => '百度信息流',
        2 => '微博',
        3 => '头条',
        4 => '抖音',
        5 => '百度竞价',
        6 => '搜狗',
        7 => '神马',
    ];

    // 表单数量基础格式
    public static $FormCountDataFormat = [
        'form_count'   => 0,
        'is_archive-0' => 0,
        'is_archive-1' => 0,
        'intention-0'  => 0,
        'intention-1'  => 0,
        'intention-2'  => 0,
        'intention-3'  => 0,
        'intention-4'  => 0,
        'intention-5'  => 0,
        'intention-6'  => 0,
    ];

    /**
     * 关联手机号码
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phones()
    {
        return $this->hasMany(FormDataPhone::class);
    }

    /**
     * 关联 项目
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    /**
     * 关联 科室
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(DepartmentType::class, 'department_id', 'id');
    }

    /**
     * 获取已关联的科室的数据
     * @return mixed
     */
    public function getDepartmentInfoAttribute()
    {
        return Helpers::checkDepartment($this->data_type);
    }

    /**
     * 获取已关联科室 下面的 病种类型.
     * @return \Illuminate\Support\Collection|null
     */
    public function getProjectInfoAttribute()
    {
        $department = $this->department_info;
        return $department ? Helpers::checkDepartmentProject($department, $this->data_type) : null;
    }

    /**
     * 创建 FormData 数据,同时生成 FormDataPhone
     * @param array  $data  数据
     * @param string $field ID字段
     * @param null   $delay 电话号码创建延迟
     * @return mixed
     */
    public static function updateOrCreateItem($data, $field, $delay = null)
    {
        // 获取id字段
        $id = $data[$field];
        // 判断所属科室,如果存在则写入ID
        $departmentType        = Helpers::checkDepartment($data['data_type']);
        $data['department_id'] = $departmentType ? $departmentType->id : null;

        // 创建 FormData Model
        $form = FormData::updateOrCreate([
            $field => $id,
        ], $data);

        // 如果科室存在 , 再判断病种
        if ($departmentType) {
            // 判断 是否所属 科室下的病种,有则写入
            $projectType = Helpers::checkDepartmentProject($departmentType, $data['data_type']);
            $form->projects()->sync($projectType ? $projectType->pluck('id') : []);
            FormDataPhone::createOrUpdateItem($form, collect($data['phone']), $delay);
        }

        // 返回Model
        return $form;
    }

}
