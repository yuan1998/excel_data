<?php

namespace App\Jobs;

use App\Exports\BaiduPlanExport;
use App\Exports\TestExport;
use App\Models\ExportDataLog;
use App\Parsers\BaiduPlanData;
use App\Parsers\ParserStart;
use Carbon\Carbon;
use Exception;
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
     * @param $modelId
     */
    public function __construct($modelId)
    {
        Log::info('导出 报表 __construct ', [$modelId]);
        $this->model = $modelId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('生成 Excel 参数 : 开始', [
            'model' => $this->model,
        ]);

        $model = ExportDataLog::find($this->model);

        if (!$model) return;
        $time        = Carbon::now();
        $requestData = json_decode($model->request_data, true);
        Log::info('生成 Excel 参数', [$requestData]);

        $model->status = 1;
        $model->save();
        $pathName = $model->path . $model->file_name;
        Log::info(' 导出 文件 Debug  ', [
            'pathName'    => $pathName,
            'requestData' => $requestData,
        ]);
        if (isset($requestData['data_type'])) {
            Log::info(' 导出 文件 Debug : 抵达 百度  ', []);
            if ($requestData['data_type'] === 'baidu_plan') {
                $baiduPlanData = new BaiduPlanData($requestData);
                $export        = new BaiduPlanExport($baiduPlanData);
                Excel::store($export, $pathName, 'public');
            }
        } else {
            Log::info(' 导出 文件 Debug : 抵达 报表  ', []);
            $parser = new ParserStart($requestData);
            $test   = Excel::store(new TestExport($parser), $pathName, 'public');
            Log::info(' 导出 文件 Debug  ', [
                'pathName' => $pathName,
                'result'   => $test,
            ]);
        }

        $model->status   = 2;
        $model->run_time = Carbon::now()->diffInSeconds($time) ?? 0;
        $model->save();
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::info('生成 Excel 参数 : 失败', [
            'model' => $this->model,
        ]);
        $model         = ExportDataLog::find($this->model);
        $model->status = 3;
        $model->save();
        Log::error('抓取数据时错误', [$model->file_name, $exception]);

    }


}
