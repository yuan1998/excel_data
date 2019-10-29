<?php

namespace App\Admin\Actions;

use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboFormDataImport;
use App\Imports\WeiboImport;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WeiboUpload extends Action
{
    public $name = '导入数据';

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
        $excel  = $request->file('excel');
        $result = Excel::import(new WeiboFormDataImport(), $excel);
        return $this->response()->success('上传成功...')->refresh();
    }


    public function form()
    {
        $this->file('excel', '请选择文件')->required();
    }


    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-primary excel-upload">导入数据</a>
HTML;
    }
}
