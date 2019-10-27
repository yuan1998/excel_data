<?php

namespace App\Observers;

use App\Jobs\ClueDataCheck;
use App\Models\FormDataPhone;
use Illuminate\Support\Facades\Redis;

class FormDataPhoneObserver
{
    /**
     * Handle the form data phone "created" event.
     *
     * @param FormDataPhone $formDataPhone
     * @return void
     */
    public function created(FormDataPhone $formDataPhone)
    {
        Redis::set($formDataPhone->getTable() . '_' . $formDataPhone->id . '_queue_clue_loading', 1);
        ClueDataCheck::dispatch($formDataPhone)->onQueue('form_data_phone');
    }

    /**
     * Handle the form data phone "updated" event.
     *
     * @param FormDataPhone $formDataPhone
     * @return void
     */
    public function updated(FormDataPhone $formDataPhone)
    {
        $change = $formDataPhone->getChanges();
        if (isset($change['phone'])) {
            Redis::set($formDataPhone->getTable() . '_' . $formDataPhone->id . '_queue_clue_loading', 1);
            ClueDataCheck::dispatch($formDataPhone)->onQueue('form_data_phone');
        }
    }

    /**
     * Handle the form data phone "deleted" event.
     *
     * @param FormDataPhone $formDataPhone
     * @return void
     */
    public function deleted(FormDataPhone $formDataPhone)
    {
        //
    }

    /**
     * Handle the form data phone "restored" event.
     *
     * @param FormDataPhone $formDataPhone
     * @return void
     */
    public function restored(FormDataPhone $formDataPhone)
    {
        //
    }

    /**
     * Handle the form data phone "force deleted" event.
     *
     * @param FormDataPhone $formDataPhone
     * @return void
     */
    public function forceDeleted(FormDataPhone $formDataPhone)
    {
        //
    }
}
