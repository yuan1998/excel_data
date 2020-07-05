<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ExportExcelRequest;
use App\Models\ExportDataLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ExcelDataLogController extends Controller
{

    public static function validateRequest($data)
    {
        $type = Arr::get($data, 'data_type', null);

        switch ($type) {
            case 'xxl_data_excel':
                $channel    = Arr::get($data, 'channel_id', null);
                $department = Arr::get($data, 'department_id', null);
                if (!$channel || !$department) return false;

                return !(!count($channel) || !count($department));
            case "consultant_group_excel":
                $channel    = Arr::get($data, 'channel_id', null);
                $department = Arr::get($data, 'department_id', null);
                $groupId    = Arr::get($data, 'consultant_group_id', null);
                if (!$channel || !$department || !$groupId) return false;

                return !(!count($channel) || !count($department));
            case 'baidu_plan':
                return true;
        }

        return false;

    }

    public function exportExcelStore(ExportExcelRequest $request)
    {
        $data = $request->all();

        if (!static::validateRequest($data))
            $this->response->errorBadRequest('错误的参数');

        $exportDataLog = ExportDataLog::generate($data);
        if (!$exportDataLog) {
            $this->response->errorBadRequest();
        }

        return $this->response->array([
            'code'    => 0,
            'message' => '创建任务成功',
        ]);
    }

    public function sanfangExportData(Request $request)
    {
        $dates = $request->get('dates');
        if (!$dates) return $this->response->array([
            'code'    => 1000,
            'message' => '错误的参数'
        ]);

        $exportDataLog = ExportDataLog::generate([
            'dates'     => $dates,
            'data_type' => 'sanfang_data_excel'
        ]);

        if (!$exportDataLog) {
            return $this->response->array([
                'code'    => 10001,
                'message' => '创建任务失败,请联系管理员'
            ]);
        }

        return $this->response->array([
            'code'    => 0,
            'message' => '成功!正在等待任务执行!'
        ]);

    }

}
