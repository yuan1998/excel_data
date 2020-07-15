<?php

namespace App\Models;

use App\Helpers;
use App\OppoSpend;
use Illuminate\Database\Eloquent\Model;
use stringEncode\Exception;

/**
 * @method static SpendData updateOrCreate(array $array, array $parseMakeSpendData)
 */
class SpendData extends Model
{
    protected $fillable = [
        'date',
        'spend',
        'off_spend',
        'spend_type',
        'show',
        'interactive',
        'click',
        'baidu_id',
        'weibo_id',
        'feiyu_id',
        'spend_name',
        'account_id',
        'account_keyword',
        'department_id',
        'model_id',
        'model_type',
        'type',

        'code',
        'data_snap',
        'channel_id',
        'uuid',
    ];

    protected $casts = [
        'data_snap' => 'json',
    ];

    public static $SpendCountDataFormat = [
        'like'        => 0,
        'share'       => 0,
        'start'       => 0,
        'follow'      => 0,
        'diversions'  => 0,
        'spend'       => 0,
        'off_spend'   => 0,
        'interactive' => 0,
        'click'       => 0,
        'show'        => 0,
    ];

    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentType::class, 'department_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(AccountData::class, 'account_id', 'id');
    }

    public function spendModel()
    {
        return $this->morphTo('model');
    }

    public function typeChannel()
    {
        return $this->belongsTo(Channel::class, 'spend_type', 'form_type');

    }

    public static function fixToDataOrigin()
    {
        $data = static::query()->with(['typeChannel', 'spendModel'])->whereNull('uuid')
            ->get();

        foreach ($data as $item) {
            $uuid  = null;
            $model = $item['model'];
            switch ($item['model_type']) {
                case MeiyouSpend::class:
                case KuaiShouSpend::class:
                    $uuid = $model['advertiser_name'];
                    break;
                case VivoSpend::class:
                    $uuid = isset($model['ad_plan_name']) ? $model['ad_plan_name'] : $item['spend_name'];
                    break;
                case BaiduSpend::class:
                    $uuid = $model['promotion_plan_id'];
                    break;
                case FeiyuSpend::class:
                    $uuid = $model['advertiser_id'];
                    break;
                case WeiboSpend::class:
                    $uuid = $model['advertiser_plan'];
                    break;
                case OppoSpend::class:
                    $uuid = $model['plan_id'];
                    break;
            }


            $value = [
                'channel_id' => $item->typeChannel->id,
                'code'       => $item->spend_name,
                'uuid'       => $uuid,
                'data_snap'  => $model->toJson(),
            ];
            $item->update($value);
        }
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
                    $model = BaiduSpend::class;
                    $id    = $item->baidu_id;
                } elseif ($item->weibo_id) {
                    $model = WeiboSpend::class;
                    $id    = $item->weibo_id;
                } elseif ($item->feiyu_id) {
                    $model = FeiyuSpend::class;
                    $id    = $item->feiyu_id;
                }

                if ($id && $model) {
                    $item->update([
                        'model_type' => $model,
                        'model_id'   => $id,
                    ]);
                }

            } catch (\Exception $exception) {
                dd($model, $item);
            }
        });
    }

    public static function itemSelfRecheck($item)
    {
        $departmentType = Helpers::checkDepartment($item['spend_name']);
        $accountKey     = $item['account_keyword'] ? 'account_keyword' : 'spend_name';
        $item['type']   = $departmentType ? $departmentType->type : null;
        $account        = Helpers::formDataCheckAccount($item, $accountKey, 'spend_type', true);
        $offSpend       = (float)$item['spend'];
        if ($account) {
            $offSpend = $offSpend / (float)$account['rebate'];
        }

        $item->update([
            'department_id' => $departmentType ? $departmentType->id : null,
            'account_id'    => $account ? $account['id'] : null,
            'off_spend'     => $offSpend,
            'type'          => $item['type'],
        ]);

        if ($departmentType) {
            $projectType = Helpers::checkDepartmentProject($departmentType, $item['spend_name'], 'spend_keyword');
            $item->projects()->sync($projectType);
        }
    }

    public static function recheckAllSpendData()
    {
        static::all()
            ->each(function ($item) {
                static::itemSelfRecheck($item);
            });
    }

    public static function fixAccountId()
    {
        $data = static::query()
            ->get();

        foreach ($data as $item) {
            $id = Helpers::formDataCheckAccount($item, 'spend_name', 'spend_type');
            $item->update([
                'account_id' => $id
            ]);
        }
    }

    public static function fixSpendDataType()
    {
        $data = static::query()->with(['department'])->get();

        foreach ($data as $item) {
            $item->update([
                'type' => $item->department->type,
            ]);
        }
    }

    public static function parseMakeSpendData($item)
    {
        $account  = Helpers::formDataCheckAccount($item, 'code', 'spend_type', true);
        $offSpend = (float)$item['spend'];
        if ($account) {
            $offSpend = $offSpend / (float)$account['rebate'];
        }

        return [
            'type'            => $item['type'],
            'department_id'   => $item['department_id'],
            'date'            => $item['date'],
            'spend_name'      => $item['code'],
            'show'            => $item['show'],
            'click'           => $item['click'],
            'spend'           => $item['spend'],
            'off_spend'       => $offSpend,
            'spend_type'      => $item['spend_type'],
            'account_id'      => $account ? $account['id'] : null,
            'account_keyword' => $item['code'],
        ];
    }

    public static function baseMakeModelData($className, $item, $nameField = 'advertiser_name', $dateField = 'date')
    {
        $model = $className::updateOrCreate([
            $nameField => $item[$nameField],
            $dateField => $item[$dateField],
        ], $item);
        $model->projects()->sync($item['project_type']);

        $spend = static::updateOrCreate([
            'model_id'   => $model->id,
            'model_type' => $className,
        ], static::parseMakeSpendData($item));

        $spend->projects()->sync($item['project_type']);

    }

    public static function parseWeiboMakeSpendData($item)
    {
        $item = static::parseMakeSpendData($item);

        return array_merge($item, [
            'interactive' => $item['interactive'],
        ]);
    }

}
