<?php

namespace App\Jobs;

use App\Helpers;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\models\CrmGrabLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class CrmGrabLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var CrmGrabLog
     */
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
        Log::info('model' , [$model]);
        if ($model && $dataModel = Helpers::getDataModel($model->model_type)) {
            $model->status = 1;
            $model->save();

            $data = $dataModel::getDataOfDate($model->type, $model->start_date, $model->end_date);
            Log::info($model->name , $data);

            $model->status = 2;
            $model->save();

        } else {

            $model->status = 1;
            $model->save();
        }
    }
}
