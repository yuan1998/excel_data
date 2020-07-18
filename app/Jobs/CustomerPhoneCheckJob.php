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
            $clientName = $model->client ?? $model->type;
            if (!$client = Helpers::typeClient($clientName)) return;

            $id   = $model->customer_id;
            $type = $model->customer_type;
            $url  = $type === 'temp_cust_info_cross' ? BaseClient::$temp_cust_info_cust_infos_url : BaseClient::$cust_info_cust_infos_url;

            $phoneList   = $client::baseCustomerInfoApi($id, $url);
            $phoneResult = [];
            foreach ($phoneList as $phone) {
                $phoneResult[] = $client::customerPhoneApi($phone['id'], $phone['type']);
            }

            $model->update([
                'phone' => implode(',', $phoneResult),
            ]);
//            $data = $client::customerPhoneCreate($model->customer_id, $model->customer_type);
            Log::info('Check Customer Phone', $phoneResult);
        }
    }
}
