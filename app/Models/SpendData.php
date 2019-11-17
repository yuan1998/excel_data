<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;
use stringEncode\Exception;

class SpendData extends Model
{
    protected $fillable = [
        'date',
        'spend',
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

    public static function recheckSpendData()
    {
        static::all()
            ->each(function ($item) {
                $departmentType = Helpers::checkDepartment($item['spend_name']);

                $item->update([
                    'department_id' => $departmentType ? $departmentType->id : null,
                    'account_id'    => Helpers::formDataCheckAccount($item, 'spend_name', 'spend_type')
                ]);

                if ($departmentType) {
                    $projectType = Helpers::checkDepartmentProject($departmentType, $item['spend_name'], 'spend_keyword');
                    $item->projects()->sync($projectType);
                }
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

}
