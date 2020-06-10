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
}
