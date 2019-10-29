<?php

namespace App\Observers;

use App\Models\WeiboUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class WeiboUserObserver
{
    /**
     * Handle the weibo user "created" event.
     *
     * @param WeiboUser $weiboUser
     * @return void
     */
    public function created(WeiboUser $weiboUser)
    {

    }

    /**
     * Handle the weibo user "updated" event.
     *
     * @param WeiboUser $weiboUser
     * @return void
     */
    public function updated(WeiboUser $weiboUser)
    {
        $changes = $weiboUser->getChanges();
        if (isset($changes['pause'])) {
            $redisUser = Redis::get('weibo_user_list');

            $user = collect($redisUser ? json_decode($redisUser) : []);
            Log::info('weibo user start', $user->toArray());


            $weiboId = $weiboUser->id; // 1
            $has     = $user->contains($weiboId);

            if ($changes['pause']) {
                $has && $user = $user->filter(function ($id) use ($weiboId) {
                    return $id != $weiboId;
                });
            } else {
                !$has && $user->push($weiboId);
            }
            Log::info('weibo user end', $user->toArray());
            Redis::set('weibo_user_list', $user->values()->toJson());
        }
    }

    /**
     * Handle the weibo user "deleted" event.
     *
     * @param WeiboUser $weiboUser
     * @return void
     */
    public function deleted(WeiboUser $weiboUser)
    {
        //
    }

    /**
     * Handle the weibo user "restored" event.
     *
     * @param WeiboUser $weiboUser
     * @return void
     */
    public function restored(WeiboUser $weiboUser)
    {
        //
    }

    /**
     * Handle the weibo user "force deleted" event.
     *
     * @param WeiboUser $weiboUser
     * @return void
     */
    public function forceDeleted(WeiboUser $weiboUser)
    {
        //
    }
}
