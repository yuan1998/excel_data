<?php

namespace App\Admin\Actions;

use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboFormDataImport;
use App\Imports\WeiboImport;
use App\Models\FormDataPhone;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RecheckFormAction extends Action
{
    public $name = '重新查询';

    protected $selector = '.excel-upload';

    /**
     * ExcelUpload constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(Request $request)
    {
        $start  = $request->get('start');
        $end  = $request->get('end');
        $count = FormDataPhone::recheckOfDate([$start, $end]);



        return $this->response()->success("有{$count}条数据开始重新查询...")->refresh();
    }


    public function form()
    {
        $this->date('start','开始时间')->required();
        $this->date('end','结束时间')->required();
    }


    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-primary excel-upload">重新查询</a>
HTML;
    }
}
