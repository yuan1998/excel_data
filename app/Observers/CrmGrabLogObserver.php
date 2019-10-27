<?php

namespace App\Observers;

use App\Jobs\CrmGrabLogJob;
use App\Models\CrmGrabLog;

class CrmGrabLogObserver
{
    /**
     * Handle the crm grab log "created" event.
     *
     * @param CrmGrabLog $crmGrabLog
     * @return void
     */
    public function created(CrmGrabLog $crmGrabLog)
    {
        CrmGrabLogJob::dispatch($crmGrabLog)->onQueue('crm_grab_log_queue');
    }

    /**
     * Handle the crm grab log "updated" event.
     *
     * @param CrmGrabLog $crmGrabLog
     * @return void
     */
    public function updated(CrmGrabLog $crmGrabLog)
    {
        //
    }

    /**
     * Handle the crm grab log "deleted" event.
     *
     * @param CrmGrabLog $crmGrabLog
     * @return void
     */
    public function deleted(CrmGrabLog $crmGrabLog)
    {
        //
    }

    /**
     * Handle the crm grab log "restored" event.
     *
     * @param CrmGrabLog $crmGrabLog
     * @return void
     */
    public function restored(CrmGrabLog $crmGrabLog)
    {
        //
    }

    /**
     * Handle the crm grab log "force deleted" event.
     *
     * @param CrmGrabLog $crmGrabLog
     * @return void
     */
    public function forceDeleted(CrmGrabLog $crmGrabLog)
    {
        //
    }
}
