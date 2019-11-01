<?php

namespace App\Jobs;

use App\Models\WeiboFormData;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class PullWeiboFormData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $startDate;
    public $endDate;
    public $count;

    /**
     * PullWeiboFormData constructor.
     * @param $startDate
     * @param $endDate
     * @param $count
     */
    public function __construct($startDate, $endDate, $count = 2000)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->count     = $count;
    }


    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $count = WeiboFormData::pullWeiboData($this->startDate, $this->endDate, $this->count);
        $time  = Carbon::now()->toTimeString();
        Log::info($time . ' pull weibo form data count', [$count]);
    }
}
