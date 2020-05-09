<?php

namespace App\Admin\Actions;

use App\Clients\WeiboClient;
use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboFormDataImport;
use App\Imports\WeiboImport;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WeiboGrab extends Action
{
    public $name = '手动拉取数据';

    protected $selector = '.excel-upload';

    /**
     * ExcelUpload constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function render()
    {
        return view('admin.actions.weiboGrabAction', ['accounts' => WeiboClient::$Account]);
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
