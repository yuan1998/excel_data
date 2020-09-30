<?php

namespace App\Jobs;

use App\Clients\BaseClient;
use App\Helpers;
use App\Models\CustomerPhone;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class CustomerPhoneCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $modelId;

    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param $modelId
     */
    public function __construct($modelId)
    {
        $this->modelId = $modelId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $model = CustomerPhone::find($this->modelId);
        if ($model) {
            $model->checkPhone();
        }
    }
}
