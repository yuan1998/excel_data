<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Contracts\JWTSubject;

class WeiboUser extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'test_pass',
        'username',
        'password',
        'pause',
        'limit',
    ];

    protected $casts = [
        'pause' => 'boolean'
    ];

    protected $hidden = [
        'password'
    ];

    public function weiboFormData()
    {
        return $this->hasOne(WeiboFormData::class, 'weibo_user_id', 'id');
    }

    public function weiboFormDataNull()
    {
        return $this->hasOne(WeiboFormData::class, 'weibo_user_id', 'id')
            ->whereNull('recall_date');
    }

    public function weiboFormDataToday()
    {
        $date = Carbon::today()->toDateString();
        return $this->hasOne(WeiboFormData::class, 'weibo_user_id', 'id')
            ->whereDate('dispatch_date', $date);
    }

    public function weiboFormDataAll()
    {
        return $this->hasOne(WeiboFormData::class, 'weibo_user_id', 'id');
    }


    public static function newDispatchData()
    {
        $data = static::query()->withCount([
            'weiboFormDataToday'
        ])
            ->where('pause', false)
            ->get();

        $data = $data->filter(function ($item) {
            return $item->weibo_form_data_today_count < $item->limit;
        });

        $data = $data->sortBy('weibo_form_data_today_count')->first();

        return $data ? $data->id : null;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
