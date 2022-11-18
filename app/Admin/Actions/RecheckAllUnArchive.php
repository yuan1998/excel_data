<?php

namespace App\Admin\Actions;

use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboImport;
use App\Models\FormDataPhone;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RecheckAllUnArchive extends Action
{
    public $name = '查询所有未建档';

    protected $selector = '.recheck-un-archive';

    /**
     * ExcelUpload constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(Request $request)
    {
        try {
            $count =  FormDataPhone::recheckUnArchive();

            return $this->response()->success("$count 条数据开始重查")->refresh();
        }catch (\Exception $exception) {
            return $this->response()->error("执行错误!");
        }
    }

    public function dialog()
    {
        $this->confirm('确认要重新查询所有未建档?');
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-primary recheck-un-archive">查询所有未建档</a>
HTML;
    }
}
