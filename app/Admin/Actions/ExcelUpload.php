<?php

namespace App\Admin\Actions;

use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboImport;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelUpload extends Action
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
        $model  = ucfirst($request->get('model'));
        $model  = "\\App\\Imports\\{$model}Import";
        $excel  = $request->file('excel');
        $klass  = new $model();
        $result = Excel::import($klass, $excel);
        return $this->response()->success('Success message...')->refresh();
    }


    public function form()
    {
        $this->radio('model', __('Model'))->options([
            'feiyu' => '飞鱼表单',
            'weibo' => '微博表单',
            'baidu' => '百度表单'
        ])->required();
        $this->file('excel', '请选择文件')->required();
    }


    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-primary excel-upload">导入数据</a>
HTML;
    }
}
