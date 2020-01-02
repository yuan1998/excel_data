<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ClueDataCheck;
use App\Models\FormDataPhone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FormDataPhoneController extends Controller
{

    public function recheckStatusOfDate(Request $request)
    {
        $start_date = $request->get('start');
        $end_date   = $request->get('end');

        $count = FormDataPhone::recheckOfDate([$start_date, $end_date]);

        return $count;
    }

}
