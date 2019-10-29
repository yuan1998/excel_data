<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
                $id   = $user->shift();
                Log::info('dispatch id:' , [$id , '$user' => $user->toArray()]);
                $user->push($id);
                Redis::set('weibo_user_list' ,$user->toJson());

                return $id;
            }
        }
        return null;
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