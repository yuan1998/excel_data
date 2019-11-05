<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
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
        'type',
    ];

    protected $casts = [
        'pause' => 'boolean'
    ];

    protected $hidden = [
        'password'
    ];

    public function weiboFormData()
    {
        return $this->hasMany(WeiboFormData::class, 'weibo_user_id', 'id');
    }

    /**
     * 关联该客服 未回访的表单
     * @return HasOne|Builder
     */
    public function weiboFormDataNull()
    {
        return $this->hasMany(WeiboFormData::class, 'weibo_user_id', 'id')
            ->whereNull('recall_date');
    }

    /**
     * 关联该客服 今天的表单
     * @return HasOne|Builder
     */
    public function weiboFormDataToday()
    {
        $date = Carbon::today()->toDateString();
        return $this->hasMany(WeiboFormData::class, 'weibo_user_id', 'id')
            ->whereDate('dispatch_date', $date);
    }

    /**
     * 关联该客服 所有的表单
     * @return HasMany
     */
    public function weiboFormDataAll()
    {
        return $this->hasMany(WeiboFormData::class, 'weibo_user_id', 'id');
    }

    public static function checkUsersIsOnline($ids)
    {
        if (!$ids || !count($ids)) return null;

        return WeiboUser::query()
            ->whereIn('id', $ids)
            ->where('is_pause', false)
            ->get();
    }

    /**
     * 获取分配用户方法 (新)
     * @param array $ids
     * @return string|int|null
     */
    public static function newDispatchData($ids)
    {
        if (!$ids || !count($ids)) return null;

        $data = static::query()
            ->withCount([
                'weiboFormDataToday'
            ])
            ->whereIn('id', $ids)
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
