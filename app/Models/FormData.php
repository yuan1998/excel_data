<?php

namespace App\Models;

use App\Helpers;
use App\Imports\BaiduImport;
use App\Imports\BaiduSpendImport;
use App\Imports\FeiyuImport;
use App\Imports\FeiyuSpendImport;
use App\Imports\WeiboFormDataImport;
use App\Imports\WeiboSpendImport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use stringEncode\Exception;

/**
 * @property mixed data_type
 * @property mixed project_info
 * @property mixed department_info
 */
class FormData extends Model
{
    protected $fillable = [
        'weibo_id',
        'baidu_id',
        'feiyu_id',
        'archive_type',
        'form_type',
        'date',
        'data_type',
        'department_id',
        'account_id',
        'account_keyword',
        'model_id',
        'model_type',
        'type',
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
        'form_count'    => 0,
        'is_repeat-0'   => 0,
        'is_repeat-1'   => 0,
        'is_repeat-2'   => 0,
        'turn_weixin-0' => 0,
        'turn_weixin-1' => 0,
        'turn_weixin-2' => 0,
        'is_archive-0'  => 0,
        'is_archive-1'  => 0,
        'intention-0'   => 0,
        'intention-1'   => 0,
        'intention-2'   => 0,
        'intention-3'   => 0,
        'intention-4'   => 0,
        'intention-5'   => 0,
        'intention-6'   => 0,
    ];

    public function formModel()
    {
        return $this->morphTo('model');
    }

    /**
     * 关联手机号码
     * @return HasMany
     */
    public function phones()
    {
        return $this->hasMany(FormDataPhone::class);
    }

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

    public function account()
    {
        return $this->belongsTo(AccountData::class, 'account_id', 'id');
    }

    public static function fixWeiboMorph()
    {
        $data = static::query()
            ->whereNull('model_type')
            ->orWhereNotNull('weibo_id')
            ->get();
        $data->each(function ($item) {
            $id    = null;
            $model = null;
            try {
                if ($item->baidu_id) {
                    $model = BaiduData::class;
                    $id    = BaiduData::query()->where('visitor_id', $item->baidu_id)->first()->id;
                } elseif ($item->weibo_id) {
                    $weibo = WeiboData::query()->where('weibo_id', $item->weibo_id)->first();
                    if ($weibo) {
                        $model = WeiboData::class;
                        $id    = $weibo->id;
                    } else {
                        $model = WeiboFormData::class;
                        $id    = $item->weibo_id;
                    }
                } elseif ($item->feiyu_id) {
                    $model = FeiyuData::class;
                    $id    = FeiyuData::query()->where('clue_id', $item->feiyu_id)->first()->id;
                }

                if ($id && $model) {
                    $item->update([
                        'model_type' => $model,
                        'model_id'   => $id,
                    ]);
                }

            } catch (Exception $exception) {
                dd($model, $item);
            }
        });

    }

    public static function fixMorph()
    {
        $data = static::query()
            ->whereNull('model_type')
            ->get();
        $data->each(function ($item) {
            $id    = null;
            $model = null;
            try {
                if ($item->baidu_id) {
                    $model = BaiduData::class;
                    $id    = BaiduData::query()->where('visitor_id', $item->baidu_id)->first()->id;
                } elseif ($item->weibo_id) {
                    $model = WeiboFormData::class;
                    $id    = $item->weibo_id;
                } elseif ($item->feiyu_id) {
                    $model = FeiyuData::class;
                    $id    = FeiyuData::query()->where('clue_id', $item->feiyu_id)->first()->id;
                }

                if ($id && $model) {
                    $item->update([
                        'model_type' => $model,
                        'model_id'   => $id,
                    ]);
                }

            } catch (Exception $exception) {
                dd($model, $item);
            }
        });

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
     * 创建 FormData 数据,同时生成 FormDataPhone
     * @param array  $data      数据
     * @param string $modelType Model
     * @param null   $delay     电话号码创建延迟
     * @return mixed
     */
    public static function updateOrCreateItem($data, $modelType, $delay = null)
    {
        // 判断所属科室,如果存在则写入ID
        $departmentType     = Helpers::checkDepartment($data['data_type']);
        $data['account_id'] = Helpers::formDataCheckAccount($data, 'data_type');

        $data['department_id'] = $departmentType ? $departmentType->id : null;

        // 创建 FormData Model
        $form = FormData::updateOrCreate([
            'model_id'   => $data['model_id'],
            'model_type' => $modelType,
        ], $data);

        // 如果科室存在 , 再判断病种
        if ($departmentType) {
            // 判断 是否所属 科室下的病种,有则写入
            $projectType = Helpers::checkDepartmentProject($departmentType, $data['data_type']);
            $form->projects()->sync($projectType);
            FormDataPhone::createOrUpdateItem($form, collect($data['phone']), $delay);
        }

        // 返回Model
        return $form;
    }

    public static function recheckFormData()
    {
        static::all()
            ->each(function ($item) {
                $departmentType = Helpers::checkDepartment($item['data_type']);
                $data           = [
                    'department_id' => $departmentType ? $departmentType->id : null,
                    'account_id'    => Helpers::formDataCheckAccount($item, 'data_type')
                ];
                $item->update($data);

                if ($item->phones->isEmpty() && $item->formModel) {
                    $phone = $item->formModel->phone;
                    FormDataPhone::createOrUpdateItem($item, collect($phone));
                }

                if ($departmentType) {
                    $projectType = Helpers::checkDepartmentProject($departmentType, $item['data_type']);
                    $item->projects()->sync($projectType);
                }

            });

    }

    /**
     * @param $model
     * @return string|null
     */
    public static function checkImportModel($model)
    {
        switch ($model) {
            case "weibo" :
                return WeiboFormDataImport::class;
            case "baidu" :
                return BaiduImport::class;
            case "feiyu":
                return FeiyuImport::class;
            case "weibo_spend" :
                return WeiboSpendImport::class;
            case "baidu_spend" :
                return BaiduSpendImport::class;
            case "feiyu_spend" :
                return FeiyuSpendImport::class;
        }
        return null;
    }

}
