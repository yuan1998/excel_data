<?php

namespace App\Observers;

use App\Jobs\ClueDataCheck;
use App\Models\FeiyuData;
use Illuminate\Support\Facades\Redis;

class FeiyuObserver
{
    /**
     * Handle the feiyu data "created" event.
     *
     * @param FeiyuData $feiyuData
     * @return void
     */
    public function created(FeiyuData $feiyuData)
    {
        Redis::set($feiyuData->getTable() . '_' . $feiyuData->id . '_queue_clue_loading', 1);
        ClueDataCheck::dispatch($feiyuData->id)->onQueue('feiyu');
    }

    /**
     * Handle the feiyu data "updated" event.
     *
     * @param FeiyuData $feiyuData
     * @return void
     */
    public function updated(FeiyuData $feiyuData)
    {
        $change = $feiyuData->getChanges();
        if (isset($change['phone'])) {
            Redis::set($feiyuData->getTable() . '_' . $feiyuData->id . '_queue_clue_loading', 1);
            ClueDataCheck::dispatch($feiyuData->id)->onQueue('feiyu');
        }
    }

    /**
     * Handle the feiyu data "deleted" event.
     *
     * @param FeiyuData $feiyuData
     * @return void
     */
    public function deleted(FeiyuData $feiyuData)
    {
        //
    }

    /**
     * Handle the feiyu data "restored" event.
     *
     * @param FeiyuData $feiyuData
     * @return void
     */
    public function restored(FeiyuData $feiyuData)
    {
        //
    }

    /**
     * Handle the feiyu data "force deleted" event.
     *
     * @param FeiyuData $feiyuData
     * @return void
     */
    public function forceDeleted(FeiyuData $feiyuData)
    {
        //
    }
}
