<?php

namespace App\Observers;

use App\Models\WeiboFormData;
use App\Models\WeiboUser;
use Illuminate\Support\Facades\Log;

class WeiboFormDataObserver
{
    /**
     * Handle the weibo form data "created" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function created(WeiboFormData $weiboFormData)
    {
        $id = WeiboUser::newDispatchData();

        Log::info('dispatcher', [$id]);
        if ($id) {
            $weiboFormData->weibo_user_id = $id;
            $weiboFormData->save();
        }
    }

    /**
     * Handle the weibo form data "updated" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function updated(WeiboFormData $weiboFormData)
    {
        //
    }

    /**
     * Handle the weibo form data "deleted" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function deleted(WeiboFormData $weiboFormData)
    {
        //
    }

    /**
     * Handle the weibo form data "restored" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function restored(WeiboFormData $weiboFormData)
    {
        //
    }

    /**
     * Handle the weibo form data "force deleted" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function forceDeleted(WeiboFormData $weiboFormData)
    {
        //
    }
}
