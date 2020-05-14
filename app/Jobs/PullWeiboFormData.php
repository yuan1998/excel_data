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
    public $type;

    public $tries = 1;

    public $timeout = 600;

    /**
     * PullWeiboFormData constructor.
     * @param     $type
     * @param     $startDate
     * @param     $endDate
     * @param int $count
     */
    public function __construct($type, $startDate, $endDate, $count = 2000)
    {
        $this->type      = $type;
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
        Log::info("运行微博拉取Job", [$this->type]);
        WeiboFormData::pullWeiboData($this->type, $this->startDate, $this->endDate, $this->count);
    }
}
