<?php

namespace App\Http\Controllers\Api;

use App\Clients\ApiClient;
use App\Clients\BaseClient;
use App\Models\FormDataPhone;
use Illuminate\Http\Request;

class FormDataPhoneController extends Controller
{

    public function recheckOfFormType(Request $request)
    {
        $data = $request->all(['dates', 'channel_id', 'type']);
        $count = FormDataPhone::recheckOfTypeAndDate($data);

        return $this->response->array([
            'code' => 0,
            'message' => "任务已受理,已有{$count}条数据开始执行重新查询."
        ]);
    }

    public function recheckStatusOfDate(Request $request)
    {
        $start_date = $request->dares('start');
        $end_date = $request->get('end');

        $count = FormDataPhone::recheckOfDate([$start_date, $end_date]);

        return $count;
    }

    public function testLogin(Request $request)
    {

        ApiClient::make();

        $response = ApiClient::login();
        return response()
            ->json([
                'status' => $response ? 1 : 0,
            ]);
    }

    public function testIsLogin(Request $request)
    {
        ApiClient::make();
        $response = ApiClient::isLogin();
        return response()
            ->json([
                'status' => $response ? 1 : 0,
            ]);
    }

    public function callPlanList()
    {
        ApiClient::make();
        $data = ApiClient::callPlanList();
        return response()
            ->json([
                'status' => 1,
                'result' => $data,
            ]);
    }

    public function callPlanEdit()
    {
        ApiClient::make();
        $data = ApiClient::callPlanEdit();
        return response()
            ->json([
                'status' => 1,
                'result' => $data,
            ]);
    }


}
