<?php

namespace App\Admin\Actions;

use App\Helpers;
use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboImport;
use App\models\CrmGrabLog;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CrmGrabData extends Action
{
    public $name = '抓取数据';

    protected $selector = '.crm-data-grab';

    /**
     * ExcelUpload constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(Request $request)
    {
        $modelType = $request->get('model_type');
        $type      = $request->get('type');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        Helpers::dateRangeForEach([$startDate, $endDate], function ($date) use ( $type, $modelType) {
            $date = $date->toDateString();
            CrmGrabLog::generate($type, $modelType, $date, $date);
        });
        return $this->response()->success('Success message...')->refresh();
    }


    public function form()
    {
        $this->select('type', __('Type'))->options(CrmGrabLog::$typeList)->required();
        $this->radio('model_type', __('Model type'))->options([
            'arrivingData'    => '到院数据',
            'billAccountData' => '业绩数据',
        ])->required();

        $this->date('start_date')->required();
        $this->date('end_date')->required();
    }


    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-primary crm-data-grab">抓取数据</a>
HTML;
    }
}
