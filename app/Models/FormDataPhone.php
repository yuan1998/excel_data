<?php

namespace App\Models;

use App\Jobs\ClueDataCheck;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormDataPhone extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'phone',
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
     */
    public static function createOrUpdateItem($model, $phone)
    {
        $id = $model->id;
        $phone->each(function ($phone) use ($id) {
            FormDataPhone::updateOrCreate([
                'phone'        => $phone,
                'form_data_id' => $id
            ]);
        });
    }

    public function formData()
    {
        return $this->belongsTo(FormData::class, 'form_data_id', 'id');

    }

    public function getTypeAttribute()
    {
        return $this->formData->type;
    }

    public function getIsBaiduAttribute()
    {
        return !!$this->formData->baidu_id;
    }


    public static function recheckArchive()
    {
        $data = FormDataPhone::where('is_archive', 0)
            ->has('formData')->get();
        $data->each(function ($phone) {
            ClueDataCheck::dispatch($phone)->onQueue('form_data_phone');
        });
        return $data->count();
    }


}
