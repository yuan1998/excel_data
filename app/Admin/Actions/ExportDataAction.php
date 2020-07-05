<?php

namespace App\Admin\Actions;

use App\Exports\TestExport;
use App\Helpers;
use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboImport;
use App\Models\Channel;
use App\Models\ConsultantGroup;
use App\models\CrmGrabLog;
use App\Models\DepartmentType;
use App\Models\ExportDataLog;
use App\Parsers\ParserStart;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportDataAction extends Action
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

    public function render()
    {
        $channelOptions         = Channel::all()->pluck('title', 'id');
        $departmentOptions      = DepartmentType::query()->with(['projects'])->select(['title', 'id'])->get();
        $consultantGroupOptions = ConsultantGroup::query()->select(['title', 'id'])->get();

        return view('admin.actions.exportDataAction', [
            'channelOptions'         => $channelOptions,
            'departmentOptions'      => $departmentOptions,
            'consultantGroupOptions' => $consultantGroupOptions,
        ]);
    }
}
