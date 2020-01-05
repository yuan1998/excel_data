<?php

namespace App\Models;

use App\Jobs\ClueDataCheck;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormDataPhone extends Model
{

    public static $InteractiveList = [

    ];

    public static $IntentionList = [
        0 => '未查询',
        1 => '未下预约单',
        2 => '一级',
        3 => '二级',
        4 => '三级',
        5 => '四级',
        6 => '五级',
    ];

    public static $IsRepeatList = [
        0 => '未查询',
        1 => '不重复',
        2 => '重复'
    ];

    public static $IsArchiveList = [
        0 => '未查询',
        1 => '已建档',
        2 => '未建档',
    ];

    public $timestamps = false;

    protected $fillable = [
        'phone',
        'turn_weixin',
        'is_repeat',
        'is_archive',
        'has_visitor_id',
        'has_url',
        'archive_type',
        'intention',
        'form_data_id',
    ];

    /**
     * @param FormData   $model
     * @param Collection $phone
     * @param null       $delay
     */
    public static function createOrUpdateItem($model, $phone, $delay = null)
    {
        $id = $model->id;
        $phone->each(function ($phone) use ($id, $delay) {
            $item = FormDataPhone::updateOrCreate([
                'phone'        => $phone,
                'form_data_id' => $id
            ]);
            if (!$item->is_archive) {
                ClueDataCheck::dispatch($item)->onQueue('form_data_phone')->delay($delay);
            }
        });
    }

    public function formData()
    {
        return $this->belongsTo(FormData::class, 'form_data_id', 'id');
    }

    public function getTypeAttribute()
    {
        $form = $this->formData;
        return $form ? $form->type : null;
    }

    public function getIsBaiduAttribute()
    {
        $form = $this->formData;
        return $form ? $this->formData->model_type === BaiduData::class : false;
    }

    public function getDateAttribute()
    {
        return $this->formData->date;
    }

    public static function recheckHandler($data)
    {
        $data->each(function ($phone) {
            ClueDataCheck::dispatch($phone)->onQueue('form_data_phone');
        });
    }

    public static function recheckUnArchive()
    {
        $data = FormDataPhone::where('is_archive', 0)
            ->has('formData')->get();
        static::recheckHandler($data);
        return $data->count();
    }

    public static function recheckAllCrmData()
    {
        $data = FormDataPhone::all();
        static::recheckHandler($data);

        return $data->count();
    }

    public static function recheckOfDate($dates)
    {
        $data = FormDataPhone::query()
            ->whereHas('formData', function ($query) use ($dates) {
                $query->whereBetween('date', [$dates]);
            })->get();
        static::recheckHandler($data);

        return $data->count();
    }


    public static function toString($item)
    {
        $result = $item['phone'];
        if ($item['is_repeat'] == 2) {
            $result .= '_' . '重单';
        } else {
            $result .= '_' . static::$IsArchiveList[$item['is_archive']];

            if ($item['is_archive'] == 1 && $item['intention'] > 1) {
                $result .= '_' . static::$IntentionList[$item['intention']];
            }
        }
        return $result;
    }


}
