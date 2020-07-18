<?php

namespace App\Observers;

use App\Jobs\CustomerPhoneCheckJob;
use App\Models\CustomerPhone;

class CustomerPhoneObserver
{
    /**
     * Handle the customer phone "created" event.
     *
     * @param \App\Models\CustomerPhone $customerPhone
     * @return void
     */
    public function created(CustomerPhone $customerPhone)
    {
        CustomerPhoneCheckJob::dispatch($customerPhone->id)->onQueue('check_customer_phone');
    }

    /**
     * Handle the customer phone "updated" event.
     *
     * @param \App\Models\CustomerPhone $customerPhone
     * @return void
     */
    public function updated(CustomerPhone $customerPhone)
    {
        //
    }

    /**
     * Handle the customer phone "deleted" event.
     *
     * @param \App\Models\CustomerPhone $customerPhone
     * @return void
     */
    public function deleted(CustomerPhone $customerPhone)
    {
        //
    }

    /**
     * Handle the customer phone "restored" event.
     *
     * @param \App\Models\CustomerPhone $customerPhone
     * @return void
     */
    public function restored(CustomerPhone $customerPhone)
    {
        //
    }

    /**
     * Handle the customer phone "force deleted" event.
     *
     * @param \App\Models\CustomerPhone $customerPhone
     * @return void
     */
    public function forceDeleted(CustomerPhone $customerPhone)
    {
        //
    }
}
