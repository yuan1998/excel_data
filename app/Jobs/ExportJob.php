<?php

namespace App\Jobs;

use App\Clients\SfClient;
use App\Exports\BaiduPlanExport;
use App\Exports\ConsultantGroupExport;
use App\Exports\TestExport;
use App\Models\ExportDataLog;
use App\Parsers\BaiduPlanData;
use App\Parsers\ParserConsultantGroup;
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
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var ExportDataLog
     */
    public $model;

    public $time;

    /**
     * Create a new job instance.
     *
     * @param $modelId
     */
    public function __construct($modelId)
    {
        Log::info('导出 报表 __construct ', [$modelId]);
        $this->time  = Carbon::now();
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

        $requestData = json_decode($model->request_data, true);
        Log::info('生成 Excel 参数', [$requestData]);

        $model->status = 1;
        $model->save();
        $pathName = $model->path . $model->file_name;
        Log::info(' 导出 文件 Debug  ', [
            'pathName'    => $pathName,
            'requestData' => $requestData,
        ]);
        $dataType = $model['data_type'];
        switch ($dataType) {
            case "consultant_group_excel":
                Log::info(' 导出 文件 Debug : 抵达 客服报表  ', []);
                $parser = new ParserConsultantGroup($requestData);
                $test   = Excel::store(new ConsultantGroupExport($parser), $pathName, 'public');
                Log::info(' 导出 文件 Debug  ', [
                    'pathName' => $pathName,
                    'result'   => $test,
                ]);
                break;
            case 'xxl_data_excel':
                Log::info(' 导出 文件 Debug : 抵达 报表  ', []);
                $parser = new ParserStart($requestData);
                $test   = Excel::store(new TestExport($parser), $pathName, 'public');
                Log::info(' 导出 文件 Debug  ', [
                    'pathName' => $pathName,
                    'result'   => $test,
                ]);
                break;
            case "baidu_plan":
                Log::info(' 导出 文件 Debug : 抵达 百度  ', []);
                $baiduPlanData = new BaiduPlanData($requestData);
                $export        = new BaiduPlanExport($baiduPlanData);
                Excel::store($export, $pathName, 'public');
                break;
            case "sanfang_data_excel":
                Log::info(' 导出 文件 Debug : 抵达 三方  ', []);
                $dates  = $requestData['dates'];
                $client = new SfClient($dates[0], $dates[1]);
                $export = $client->makeExcel();
                Excel::store($export, $pathName, 'public');
                break;
        }


        $model->status   = 2;
        $model->run_time = Carbon::now()->diffInSeconds($this->time) ?? 0;
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
        $model           = ExportDataLog::find($this->model);
        $model->status   = 3;
        $model->run_time = Carbon::now()->diffInSeconds($this->time) ?? 0;
        $model->save();
        Log::error('抓取数据时错误', [$model->file_name, $exception]);

    }


}
