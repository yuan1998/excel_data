<?php

namespace App\Jobs;

use App\Models\WeiboAccounts;
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
    use Dispatchable, InteractsWithQueue, Queueable;

    public $startDate;
    public $endDate;
    public $count;
    public $accountId;

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
        $this->accountId = $type;
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
        if (!$account = WeiboAccounts::find($this->accountId)) {
            Log::error('拉取微博账户表单 : 账户不存在.', [$this->accountId]);
            return;
        }

        if ($account->enable_cpl) $account->pullFormDataOfType(WeiboAccounts::$_CPL_NAME_, $this->startDate, $this->endDate, $this->count);
        if ($account->enable_lingdong) $account->pullFormDataOfType(WeiboAccounts::$_LINGDONG_NAME_, $this->startDate, $this->endDate, $this->count);
    }
}
