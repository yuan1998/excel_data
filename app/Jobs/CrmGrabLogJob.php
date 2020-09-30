<?php

namespace App\Jobs;

use App\Helpers;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\models\CrmGrabLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class CrmGrabLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var CrmGrabLog
     */
    public $id;

    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($model = CrmGrabLog::find($this->id)) {
            if ($dataModel = Helpers::getDataModel($model->model_type)) {
                $model->status = 1;
                $model->save();

                try {
                    $data = $dataModel::getDataOfDate($model->type, $model->start_date, $model->end_date);
                    Log::info($model->name, $data);
                    $model->status = 2;
                } catch (Exception $exception) {
                    Log::error('抓取数据时错误', [$model->model_type, $exception]);
                    $model->status = 3;
                }

            } else {
                $model->status = 1;
            }
            $model->save();
        }

    }


    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        $model = CrmGrabLog::find($this->id);
        if ($model) {
            $model->status = 3;
            $model->save();
        }
        // Send user notification of failure, etc...
    }
}
