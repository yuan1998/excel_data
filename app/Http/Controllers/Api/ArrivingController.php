<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UploadRequest;
use App\Imports\BaiduImport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ArrivingController extends Controller
{

    public  function uploadExcel(UploadRequest $request) {
        $type = $request->get('type');
        Excel::import(new BaiduImport($type), $request->file('excel'));

        return $this->response->noContent();

    }
}
