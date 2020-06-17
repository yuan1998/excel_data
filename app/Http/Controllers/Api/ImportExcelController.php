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

        $import  = new AutoImport();
        $message = '';
        $type    = null;
        try {
            Excel::import($import, $excel);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
        }
        $type = $import->getModelType();
        if (!$type) {
            $message = '无法识别该文件类型,如果确认数据正确,请联系管理员';
        }

        return $this->response->array([
            'type'    => $type,
            'message' => $message,
            'count'   => $import->count,
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

}
