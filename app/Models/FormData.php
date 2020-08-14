<?php

namespace App\Models;

use App\Helpers;
use App\Imports\BaiduImport;
use App\Imports\BaiduSpendImport;
use App\Imports\FeiyuImport;
use App\Imports\FeiyuSpendImport;
use App\Imports\OppoSpendImport;
use App\Imports\WeiboFormDataImport;
use App\Imports\WeiboSpendImport;
use App\Imports\YiliaoImport;
use App\Jobs\ClueDataCheck;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use stringEncode\Exception;
use Illuminate\Support\Facades\Redis;

/**
 * @property mixed data_type
 * @property mixed project_info
 * @property mixed department_info
 * @method  static FormData updateOrCreate(array $array, array $parseFormData)
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

        'consultant_code',
        'consultant_id',
        'code',
        'phone',
        'channel_id',
        'data_snap',
    ];

    protected $casts = [
        'data_snap' => 'json',
    ];

    // 平台类型列表
    public static $FormTypeList = [
        1  => '百度信息流',
        2  => '微博',
        3  => '头条',
        4  => '抖音',
        5  => '百度竞价',
        6  => '搜狗',
        7  => '神马',
        8  => 'oppo',
        9  => 'vivo',
        10 => '美柚',
        11 => '穿山甲',
        12 => '快手',
    ];

    public static $FORM_TYPE_BAIDU_XXL = 1;
    public static $FORM_TYPE_WEIBO = 2;
    public static $FORM_TYPE_TOUTIAO = 3;
    public static $FORM_TYPE_DOUYIN = 4;
    public static $FORM_TYPE_BAIDU_WEB = 5;
    public static $FORM_TYPE_SOGOU = 6;
    public static $FORM_TYPE_SHENMA = 7;
    public static $FORM_TYPE_OPPO = 8;
    public static $FORM_TYPE_VIVO = 9;
    public static $FORM_TYPE_MEIYOU = 10;
    public static $FORM_TYPE_CHUANSHANJIA = 11;
    public static $FORM_TYPE_KUAISHOU = 12;

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
        'is_archive-2'  => 0,
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

    public function typeChannel()
    {
        return $this->belongsTo(Channel::class, 'form_type', 'form_type');

    }

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id', 'id');
    }

    public static function fixToDataOrigin()
    {
        $data = static::query()
            ->with(['typeChannel', 'formModel', 'phones'])
            ->has('typeChannel')
            ->whereNull('phone')
            ->get();

        foreach ($data as $item) {
            $value = [
                'channel_id' => $item->typeChannel->id,
                'code'       => $item->data_type,
                'phone'      => $item->phones()->pluck('phone')->join(','),
                'data_snap'  => $item['model'] ? $item['model']->toJson() : null,
            ];
            $item->update($value);
        }
    }

    public static function fixConsultantInfo()
    {
        $data = static::query()
            ->with(['formModel'])
            ->whereHasMorph('formModel', [
                BaiduData::class,
                WeiboFormData::class,
                WeiboData::class,
                FeiyuData::class,
                YiliaoData::class,
                VivoData::class,
                KuaiShouData::class,
                MeiyouData::class,
            ])
            ->whereNull('consultant_code')
            ->get();

        foreach ($data as $item) {
            $modelType = $item['model_type'];
            $code      = null;
            $id        = null;
            $model     = $item['model'];
            switch ($modelType) {
                case BaiduData::class:
                    $code = $model['visitor_name'];
                    break;
                case KuaiShouData::class:
                    $code = $model['comment'];
                    break;
                case FeiyuData::class:
                    $code = $model['owner'];
                    break;
                case WeiboData::class:
                    $code = $model['category_type'];
                    break;
                case WeiboFormData::class:
                    $consultant = Consultant::query()
                        ->whereHas('weiboUser', function ($query) use ($model) {
                            $query->where('id', $model['weibo_user_id']);
                        })->first();
                    if ($consultant && $consultant['consultant_id']) {
                        $code = $consultant['name'];
                        $id   = $consultant['id'];
                    }
                    break;
                case YiliaoData::class:
                    $code = $item['name'];
                    break;
            }

            if (!$id && $code) {
                $id = Helpers::checkConsultantNameOf($item['type'], $code);
            }
            $item->update([
                'consultant_id'   => $id,
                'consultant_code' => $code,
            ]);
        }

        return $data->count();

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

    public static function isModel($data, $fields)
    {
        $keys  = array_keys($fields);
        $first = $data->get(0);

        $diff = $first->diff($keys);

        $count = $diff->count();
        return $count <= 1;
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
     * @param array $data  数据
     * @param null  $delay 电话号码创建延迟
     * @return mixed
     */
    public static function updateOrCreateItem($data, $delay = null)
    {
        // 判断所属科室,如果存在则写入ID
        $departmentType     = Helpers::checkDepartment($data['code']);
        $data['account_id'] = Channel::checkAccountByChannelId($data['channel_id'], $data['type'], $data['code']);

        $data['department_id'] = $departmentType ? $departmentType->id : null;

        // 创建 FormData Model
        $form = FormData::updateOrCreate([
            'channel_id' => $data['channel_id'],
            'date'       => $data['date'],
            'phone'      => $data['phone'],
        ], $data);

        FormDataPhone::createOrUpdateItem($form, collect($data['phone']), $delay);
        // 如果科室存在 , 再判断病种
        if ($departmentType) {
            // 判断 是否所属 科室下的病种,有则写入
            $projectType = Helpers::checkDepartmentProject($departmentType, $data['data_type']);
            $form->projects()->sync($projectType);
        }

        // 返回Model
        return $form;
    }

    public static function recheckMonthPhoneStatus()
    {
        $date  = Carbon::today();
        $start = $date->firstOfMonth()->toDateString();
        $end   = $date->lastOfMonth()->toDateString();
        static::recheckPhoneArchiveStatus($start, $end);
    }

    public static function recheckPhoneArchiveStatus($startDay, $endDay)
    {
        static::with(['phones'])
            ->whereBetween('date', [$startDay, $endDay])
            ->get()
            ->each(function ($item) {
                $item->itemRecheckPhone();
            });
    }

    public function itemRecheckPhone()
    {
        if ($this->phones->isNotEmpty()) {
            foreach ($this->phones as $phone) {
                ClueDataCheck::dispatch($phone->id)->onQueue('form_data_phone');
            }
        }
    }

    public function itemRecheck()
    {
        $departmentType = Helpers::checkDepartment($this['data_type']);
        $data           = [
            'department_id' => $departmentType ? $departmentType->id : null,
            'account_id'    => Helpers::formDataCheckAccount($this, 'data_type')
        ];
        $this->update($data);

        if ($this->formModel) {
            $phone = $this->formModel->phone;
            FormDataPhone::createOrUpdateItem($this, collect($phone));
        }

        if ($departmentType) {
            $projectType = Helpers::checkDepartmentProject($departmentType, $this['data_type']);
            $this->projects()->sync($projectType);
        }
    }

    public static function recheckFormData()
    {
        static::all()
            ->each(function ($item) {
                $item->itemRecheck();
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
            case "yiliao":
                return YiliaoImport::class;
            case "weibo_spend" :
                return WeiboSpendImport::class;
            case "baidu_spend" :
                return BaiduSpendImport::class;
            case "feiyu_spend" :
                return FeiyuSpendImport::class;
            case "oppo_spend" :
                return OppoSpendImport::class;
        }
        return null;
    }

    public static function parseFormDataDateFormat()
    {
        static::all()
            ->each(function ($item) {
                $item->date = Carbon::parse($item->date)->toDateString();
                $item->save();
            });
    }

    public static function parseFormData($item)
    {
        $date           = Carbon::parse($item['date'])->toDateString();
        $consultantCode = Arr::get($item, 'consultant_code', null);
        $consultantId   = Helpers::checkConsultantNameOf($item['type'], $consultantCode);

        return [
            'data_type'       => $item['code'],
            'form_type'       => $item['form_type'],
            'type'            => $item['type'],
            'department_id'   => $item['department_id'],
            'date'            => $date,
            'account_id'      => Helpers::formDataCheckAccount($item, 'code'),
            'account_keyword' => $item['code'],
            'consultant_code' => $consultantCode,
            'consultant_id'   => $consultantId,
        ];
    }

    public static function baseMakeFormData($className, $item, $condition)
    {
        $value   = implode('_', $condition);
        $lockKey = "{$className}_LOCK_{$value}";
        if (Redis::exists($lockKey))
            return;
        Redis::setex($lockKey, 20, 1);

        $model = $className::updateOrCreate($condition, $item);
        $model->projects()->sync($item['project_type']);

        $phone = $item['phone'];
        if ($phone) {
            $form  = static::updateOrCreate(
                [
                    'model_id'   => $model->id,
                    'model_type' => $className,
                ], static::parseFormData($item));
            $phone = BaiduData::parseClue($phone);
            FormDataPhone::createOrUpdateItem($form, $phone);
            $form->projects()->sync($item['project_type']);
        }
    }


}
