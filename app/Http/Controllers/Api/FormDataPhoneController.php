<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ClueDataCheck;
use App\Models\FormDataPhone;
use Illuminate\Http\Request;

class FormDataPhoneController extends Controller
{

    public function recheckOfFormType(Request $request)
    {
        $data  = $request->all(['dates', 'form_type', 'type']);
        $count = FormDataPhone::recheckOfTypeAndDate($data);

        return $this->response->array([
            'code'    => 0,
            'message' => "任务已受理,已有{$count}条数据开始执行重新查询."
        ]);
    }

    public function recheckStatusOfDate(Request $request)
    {
        $start_date = $request->dares('start');
        $end_date   = $request->get('end');

        $count = FormDataPhone::recheckOfDate([$start_date, $end_date]);

        return $count;
    }

}
