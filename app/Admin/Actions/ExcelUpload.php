<?php

namespace App\Admin\Actions;

use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboImport;
use App\Models\FormData;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelUpload extends Action
{
    public $name = '导入数据';

    protected $selector = '.excel-upload';

    public $models;


    /**
     * ExcelUpload constructor.
     * @param $models
     */
    public function __construct($models)
    {
        parent::__construct();
        $this->models = $models;
    }

    public function render()
    {
        return view('admin.actions.actionExcelUpload', [
            'models' => $this->models
        ]);
    }

}
