<?php

namespace App\Models;

use App\Clients\KqClient;
use App\Clients\ZxClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BaiduClue extends Model
{
    public $timestamps = false;
    protected $table = 'BaiduClues';
    protected $fillable = [
        'phone',
        'type',
        'baidu_id',
        'intention',
        'is_archive',
        'arriving_type',
        'has_dialog_id',
        'has_url',
    ];

    public function baiduData()
    {
        return $this->belongsTo(BaiduData::class, 'baidu_id', 'id');
    }

    public function getDate()
    {
        return $this->baiduData->cur_access_time;
    }

}
