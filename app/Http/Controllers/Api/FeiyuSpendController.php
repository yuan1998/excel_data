<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UploadRequest;
use App\Imports\BaiduSpendImport;
use App\Imports\FeiyuSpendImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FeiyuSpendController extends Controller
{
    public $modelName = '\\App\\Models\\FeiyuSpend';

    public function uploadExcel(UploadRequest $request)
    {
        $type = $request->get('type');

        try {
            Excel::import(new FeiyuSpendImport($type), $request->file('excel'));
        } catch (\Exception $exception) {
            $this->response->errorBadRequest($exception->getMessage());
        }


        return $this->response->noContent();
    }

}
