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
            $type = collect($import->models)->unique()->join(',');

            if (!$type) {
                return $this->response->array([
                    'code'    => 10002,
                    'message' => '无法判断该文件,请补充数据源或者检查文件表头.',
                ]);
            }

            return $this->response->array([
                'code' => 0,
                'type' => $type,
                'log'  => [
                    'success_log' => $import->importSuccessLog,
                    'fail_log'    => $import->importFailLog,
                ]
            ]);
        } catch (\Exception $exception) {
            throw $exception;
            return $this->response->array([
                'code'    => 10001,
                'message' => $exception->getMessage()
            ]);
        }
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

}
