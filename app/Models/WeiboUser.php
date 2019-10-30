<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Contracts\JWTSubject;

class WeiboUser extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'username',
        'password',
        'pause',
        'test_pass',
        'limit',
    ];

    protected $casts = [
        'pause' => 'boolean'
    ];

    protected $hidden = [
        'password'
    ];

    public static function dispatchFormData()
    {
        $redisUser = Redis::get('weibo_user_list');
        Log::info('$redisUser', [$redisUser]);

        if ($redisUser && $user = collect(json_decode($redisUser))) {
            if ($user->isNotEmpty()) {
                $id = $user->shift();

                if (static::userIsLimit($id)) {
                    $id = null;
                } else {
                    $user->push($id);
                }

                static::setUserList($user);

                return $id ? $id : static::dispatchFormData();
            }
        }
        return null;
    }

    public static function userIsLimit($id)
    {
        $count = WeiboFormData::query()
            ->where('weibo_user_id', $id)
            ->whereNull('recall_date')
            ->count();

        if ($count && $user = static::find($id)) {
            if ((int)$user->limit >= $count) {
                $user->pause = true;
                $user->save();
                
                return true;
            }
        }

        return false;
    }

    public static function userListInit()
    {
        static::query()
            ->update([
                'pause' => 1
            ]);
        static::setUserList(collect());

    }


    public static function getUserList()
    {
        $redisUser = Redis::get('weibo_user_list');
        $result    = collect($redisUser ? json_decode($redisUser) : []);
        Log::info("user dispatch list :", $result->toArray());
        return $result;
    }

    public function userAddToDispatchList()
    {
        $list = static::getUserList();
        if (!$list->contains($this->id)) {
            $list->push($this->id);
            static::setUserList($list);
        }
    }

    public function userRemoveToDispatchList()
    {
        $list = static::getUserList();

        if ($list->contains($this->id)) {
            $list = $list->filter(function ($id) {
                return $id != $this->id;
            });
            static::setUserList($list);
        }
    }

    /**
     * @param Collection $list
     */
    public static function setUserList($list)
    {
        Log::info("user dispatch list :", $list->toArray());
        Redis::set('weibo_user_list', $list->values()->toJson());
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
