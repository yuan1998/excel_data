<?php

namespace App\Jobs;

use App\Exports\TestExport;
use App\Models\ExportDataLog;
use App\Parsers\ParserStart;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 620;

    /**
     * @var ExportDataLog
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

        if ($model) {
            $requestData = json_decode($model->request_data, true);
//            dd($requestData);
            Log::info('生成 Excel 参数', [$requestData]);
            $parser        = new ParserStart($requestData);
            $pathName      = $model->path . $model->file_name;
            $model->status = 1;
            $model->save();
            Excel::store(new TestExport($parser), $pathName, 'public');
            $model->status = 2;
            $model->save();
            return;
        }
        $model->status = 3;
        $model->save();
    }
}
