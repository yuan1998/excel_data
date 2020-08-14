<?php

namespace App\Observers;

use App\Jobs\ClueDataCheck;
use App\Models\BaiduClue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BaiduClueObserver
{
    /**
     * Handle the baidu clue "created" event.
     *
     * @param \App\Models\BaiduClue $baiduClue
     * @return void
     */
    public function created(BaiduClue $baiduClue)
    {
        Redis::set($baiduClue->getTable() . '_' . $baiduClue->id . '_queue_clue_loading', 1);
        ClueDataCheck::dispatch($baiduClue->id)->onQueue('baidu');
    }

    /**
     * Handle the baidu clue "updated" event.
     *
     * @param \App\Models\BaiduClue $baiduClue
     * @return void
     */
    public function updated(BaiduClue $baiduClue)
    {
        $change = $baiduClue->getChanges();
        if (isset($change['phone'])) {
            Redis::set($baiduClue->getTable() . '_' . $baiduClue->id . '_queue_clue_loading', 1);
            ClueDataCheck::dispatch($baiduClue->id)->onQueue('baidu');
        }

    }

    /**
     * Handle the baidu clue "deleted" event.
     *
     * @param \App\Models\BaiduClue $baiduClue
     * @return void
     */
    public function deleted(BaiduClue $baiduClue)
    {
        //
    }

    /**
     * Handle the baidu clue "restored" event.
     *
     * @param \App\Models\BaiduClue $baiduClue
     * @return void
     */
    public function restored(BaiduClue $baiduClue)
    {
        //
    }

    /**
     * Handle the baidu clue "force deleted" event.
     *
     * @param \App\Models\BaiduClue $baiduClue
     * @return void
     */
    public function forceDeleted(BaiduClue $baiduClue)
    {
        //
    }
}
