<?php

namespace App\Http\Controllers\Api;

use App\Helpers;
use App\Http\Requests\ExportExcelRequest;
use App\Http\Requests\ImportExcelRequest;
use App\Imports\AutoImport;
use App\Models\ExportDataLog;
use App\Models\FormData;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportExcelController extends Controller
{

    public function uploadAutoExcel(Request $request)
    {
        $excel = $request->file('excel');
        if (!$excel) $this->response->errorBadRequest('表单文件不存在.');
        Helpers::checkUTF8($excel);

        $import = new AutoImport();
        try {
            Excel::import($import, $excel);
        } catch (\Exception $exception) {
            return $this->response->array([
                'type'    => $import->getModelType(),
                'message' => $exception->getMessage(),
            ]);
        }
        
        return $this->response->array([
            'type'  => $import->getModelType(),
            'count' => $import->count,
        ]);
    }

    public function uploadFormDataExcel(ImportExcelRequest $request)
    {
        $model = FormData::checkImportModel($request->get('model'));
        if (!$model) $this->response->errorBadRequest('错误的模型Type.');

        $excel = $request->file('excel');
        if (!$excel) $this->response->errorBadRequest('表单文件不存在.');
        Helpers::checkUTF8($excel);

        $model = new $model();
        Excel::import($model, $excel);
        return $this->response->array([
            'count' => $model->count
        ]);
    }


    public function exportExcelStore(ExportExcelRequest $request)
    {
        $data = $request->only(['department_id', 'channel_id', 'dates']);

        $exportDataLog = ExportDataLog::generate($data);
        if (!$exportDataLog) {
            $this->response->errorBadRequest();
        }

        return $this->response->array([0]);
    }

}
