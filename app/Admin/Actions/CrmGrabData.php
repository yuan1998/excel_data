<?php

namespace App\Admin\Actions;

use App\Helpers;
use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboImport;
use App\Models\Channel;
use App\models\CrmGrabLog;
use App\Models\DepartmentType;
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
        $types  = $request->get('types');
        $models = $request->get('models');
        $dates  = $request->get('dates');

        Helpers::dateRangeForEach($dates, function ($date) use ($types, $models) {
            $date = $date->toDateString();
            foreach ($models as $modelType) {
                foreach ($types as $type) {
                    CrmGrabLog::generate($type, $modelType, $date, $date);
                }
            }
        });
        return $this->response()->success('Success message...')->refresh();
    }

    public function render()
    {
        $types  = CrmGrabLog::$typeList;
        $models = CrmGrabLog::$modelTypeList;
        return view('admin.actions.grabDataFormAction', [
            'types'  => $types,
            'models' => $models
        ]);
    }
}
