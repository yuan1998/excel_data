<?php

namespace App\Jobs;

use App\Models\BillAccountData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BillAccountDataYesterday implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $type;

    /**
     * BillAccountDataGet constructor.
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        BillAccountData::yesterdayBillAccountData($this->type);
    }
}
