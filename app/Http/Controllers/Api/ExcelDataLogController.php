<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ExportExcelRequest;
use App\Models\ExportDataLog;
use Illuminate\Http\Request;

class ExcelDataLogController extends Controller
{

    public function exportExcelStore(ExportExcelRequest $request)
    {
        $data = $request->only(['department_id', 'channel_id', 'dates', 'type']);

        $data['data_type'] = 'xxl_data_excel';

        $exportDataLog = ExportDataLog::generate($data);
        if (!$exportDataLog) {
            $this->response->errorBadRequest();
        }

        return $this->response->array([0]);
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
