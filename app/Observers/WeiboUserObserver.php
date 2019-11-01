<?php

namespace App\Observers;

use App\Models\WeiboFormData;
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
//        WeiboFormData::unallocated();
    }

    /**
     * Handle the weibo user "deleted" event.
     *
     * @param WeiboUser $weiboUser
     * @return void
     */
    public function deleted(WeiboUser $weiboUser)
    {
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
