<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UploadRequest;
use App\Imports\BaiduImport;
use App\Imports\WeiboImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WeiboController extends Controller
{

    public function uploadExcel(UploadRequest $request)
    {
        $type = $request->get('type');
        Excel::import(new WeiboImport($type), $request->file('excel'));
        return $this->response->noContent();
    }

}
