<?php

namespace App\Admin\Actions;

use App\Models\Channel;
use App\Models\FormDataPhone;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

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
        $start = $request->get('start');
        $end   = $request->get('end');
        $count = FormDataPhone::recheckOfDate([$start, $end]);

        return $this->response()->success("有{$count}条数据开始重新查询...")->refresh();
    }

    public function render()
    {
        $channel =  Channel::query()->pluck('title', 'id');
        return view('admin.actions.actionRecheck', [
            'formTypeList' => $channel,
        ]);
    }

}
