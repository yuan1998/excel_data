<?php

namespace App\Clients;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PHPHtmlParser\Dom;

class ApiClient extends BaseClient
{

    public static function make()
    {
        $domain = request()->get('domain');
        $username = request()->get('username');
        $password = request()->get('password');
        if (!$domain)
            throw new \Exception('Api CLient 登录错误,域名不能为空');

        if (!$username || !$password)
            throw new \Exception('Api CLient 登录错误,账号信息不能为空');


        static::setBaseUrl($domain);
        static::$account = [
            'username' => $username,
            'password' => $password,
        ];
    }

    public static function callPlanList()
    {
        if (!static::loginStatus())
            throw new \Exception('登录失败');

        $date = Carbon::today()->toDateString();
        $client = static::getClient();
        $result = $client->request("POST", '/ReturnCall/ReservationReturnCallPlan/CustIndex',
            [
                'form_params' => [
                    'DatetimePlanStart' => request()->get('start_date', $date),
                    'DatetimePlanEnd' => request()->get('end_date', $date),
                    'DatetimeRecallStart' => '',
                    'DatetimeRecallEnd' => '',
                    'DatetimeCreatedStart' => '',
                    'DatetimeCreatedEnd' => '',
                    'LastRecallDateStart' => '',
                    'LastRecallDateEnd' => '',
                    'DatetimeCreatedsStart' => '',
                    'DatetimeCreatedsEnd' => '',
                    'TmpCustRegType' => '',
                    'TmpCustRegTypeMenusRecall' => '',
                    'RecallCountMin' => '',
                    'RecallCountMax' => '',
                    'EffectiveRecallCountMin' => '',
                    'EffectiveRecallCountMax' => '',
                    'CustName' => '',
                    'Phone' => '',
                    'KeyWords' => '',
                    'RecallPurpose' => '',
                    'Feedback' => '',
                    'PlanExecuteByName' => '',
                    'IsRecordcount' => '',
                    'ExecuteByName' => '',
                    'PlanExecuteDept' => '',
                    'PlanExecuteDeptMenus' => '',
                    'ExecuteDept' => '',
                    'ExecuteDeptMenus' => '',
                    'RecallStatus' => '0',
                    'RecallLevel' => '',
                    'RecallStage' => '',
                    'IsOnceOnly' => '',
                    'IsTerminate' => '',
                    'TerminateReason' => '',
                    'IsVisit' => '',
                    'Result' => '',
                    'IsGetThrough' => '',
                    'NotGetReason' => '',
                    'OrderBy' => '',
                    'ConsultantId' => '',
                    'ServiceCustomerId' => '',
                    'ServiceAssistantId' => '',
                    'IsBlock' => '',
                    'City' => '0',
                    'EmpStatus' => '',
                    'isSearch' => '1',
                    'iscompany' => '0',
                    'DataModel' => 'ToPageList',
                    'pageSize' => request()->get('pageSize', '50'),
                    'pageCurrent' => request()->get('page', '1'),
                    'orderField' => '',
                    'orderDirection' => '',
                    'total' => ''
                ]
            ]);
        $response = $result->getBody()->getContents();
        $test = preg_match("/type=\"text\/javascript\"/", $response);
        if (!$test) {
            Log::debug('debug 请求返回数据错误', [
                $response
            ]);
        }

        $dom = new Dom;
        $table = $dom->load($response)->find('.tableContent');
        $result = [];

        if (!$table || !count($table))
            return $result;

        $head = $table->find('thead');
        $body = $table->find('tbody');
        $ths = $head->find('th');
        $trs = $body->find('tr');

        $keys = [];
        foreach ($ths as $th) {
            $keys[] = $th->text;
        }
        foreach ($trs as $tr) {
            $arr = [];

            $td = $tr->find('td');
            $planId = $tr->getAttribute('data-id');
            if (!$planId) continue;
            $arr['id'] = $planId;

            foreach ($td as $index => $value) {
                if (!$name = data_get($keys, $index))
                    continue;

                $valueText = $value->innerHTML;
                $arr[$name] = trim(strip_tags($valueText));
            }
            array_push($result, $arr);
        }

        return $result;
    }

    public static function callPlanEdit()
    {
        $id = request()->get('id');
        if (!$id)
            throw new \Exception('ApiClient 添加回访错误: 没有ID');

        if (!static::loginStatus())
            throw new \Exception('登录失败');


        $randomDate = Carbon::now()
            ->minutes(random_int(1, 58))
            ->hours(random_int(10, 20));

        $client = static::getClient();
        $result = $client->request("POST", '/ReturnCall/ReservationReturnCallPlan/Edit/', [
            'form_params' => [
                'refreshTab' => 'ReturnCallReservationReturnCallPlanCustIndex',
                'Id' => $id,
                'datetimeRecall' => $randomDate->toDateTimeString(),
                'IsGetThrough' => request()->get('call_result', '0'),
                'Result' => request()->get('call_level', 'RR_YOUXIAOSIJI'),
                'NotGetReason' => '',
                'Feedback' => request()->get('feedback', '跟进'),
                'OnceOnly' => 'N',
                'DatetimePlanNext' => $randomDate->addDays(6)->toDateTimeString(),
                'RecallLevels' => request()->get('recall_level', '1'),
                'RecallPurposes' => '',
                'TerminateReason' => '',
                'TerminateText' => '',
            ]
        ]);
        $response = $result->getBody()->getContents();
        return json_decode($response, true);


    }

}
