<?php

namespace App\Observers;

use App\Jobs\ClueDataCheck;
use App\Models\WeiboData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class WeiboDataObserver
{
    /**
     * Handle the weibo data "created" event.
     *
     * @param WeiboData $weiboData
     * @return void
     */
    public function created(WeiboData $weiboData)
    {
        Redis::set($weiboData->getTable() . '_' . $weiboData->id . '_queue_clue_loading', 1);
        ClueDataCheck::dispatch($weiboData->id)->onQueue('weibo');
    }

    /**
     * Handle the weibo data "updated" event.
     *
     * @param WeiboData $weiboData
     * @return void
     */
    public function updated(WeiboData $weiboData)
    {
        $change = $weiboData->getChanges();
        if (isset($change['phone'])) {
            Redis::set($weiboData->getTable() . '_' . $weiboData->id . '_queue_clue_loading', 1);
            ClueDataCheck::dispatch($weiboData->id)->onQueue('weibo');
        }
    }

    /**
     * Handle the weibo data "deleted" event.
     *
     * @param WeiboData $weiboData
     * @return void
     */
    public function deleted(WeiboData $weiboData)
    {
        //
    }

    /**
     * Handle the weibo data "restored" event.
     *
     * @param WeiboData $weiboData
     * @return void
     */
    public function restored(WeiboData $weiboData)
    {
        //
    }

    /**
     * Handle the weibo data "force deleted" event.
     *
     * @param WeiboData $weiboData
     * @return void
     */
    public function forceDeleted(WeiboData $weiboData)
    {
        //
    }
}
