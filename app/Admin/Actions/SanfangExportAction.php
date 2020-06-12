<?php

namespace App\Admin\Actions;

use App\Exports\TestExport;
use App\Helpers;
use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboImport;
use App\Models\Channel;
use App\models\CrmGrabLog;
use App\Models\DepartmentType;
use App\Models\ExportDataLog;
use App\Parsers\ParserStart;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SanfangExportAction extends Action
{
    public $name = '导出数据';

    protected $selector = '.export-data-action';

    /**
     * ExcelUpload constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(Request $request)
    {
        $data = $request->only(['department_id', 'channel_id', 'dates']);

        $exportDataLog = ExportDataLog::generate($data);
        if ($exportDataLog) {
            return $this->response()->success('创建成功,已加入生成队列.')->refresh();
        }
    }


    public function render()
    {
        return view('admin.actions.sanfangExport');
    }
}
