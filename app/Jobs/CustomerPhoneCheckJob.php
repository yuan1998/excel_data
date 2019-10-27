<?php

namespace App\Jobs;

use App\Clients\BaseClient;
use App\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class CustomerPhoneCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $model;

    /**
     * Create a new job instance.
     *
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $model = $this->model;
        if ($model) {
            $client = Helpers::typeClient($model->type);
            $data   = $client::customerPhoneCreate($model->customer_id);
            Log::info('Check Customer Phone', [$data]);
        }
    }
}
