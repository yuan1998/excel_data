<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UploadRequest;
use App\Imports\BaiduSpendImport;
use App\Imports\WeiboSpendImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WeiboSpendController extends Controller
{
    public $modelName = '\\App\\Models\\WeiboSpend';

    public function uploadExcel(UploadRequest $request)
    {
        $type = $request->get('type');

        try {
            Excel::import(new WeiboSpendImport($type), $request->file('excel'));
        } catch (\Exception $exception) {
            $this->response->errorBadRequest($exception->getMessage());
        }

        return $this->response->noContent();
    }
}
