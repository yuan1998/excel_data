<?php

namespace App\Jobs;

use App\Clients\WeiboClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class WeiboAccountClientLoginJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $accountId;

    /**
     * Create a new job instance.
     *
     * @param $accountId
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $weiboClient = new WeiboClient($this->accountId);

        if ($weiboClient->account) {
            $loginStatus = $weiboClient->mapClientToLogin();

            $weiboClient->account->login_status = $loginStatus ? 1 : 0;
            $weiboClient->account->save();
        }


    }
}
