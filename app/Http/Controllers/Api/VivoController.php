<?php

namespace App\Http\Controllers\Api;

use App\Helpers;
use App\Imports\AutoImport;
use App\Models\SpendData;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VivoController extends Controller
{

    public function import(Request $request)
    {
        $file = $request->file('excel');
        Helpers::checkUTF8($file);

        $import = new AutoImport();
        Excel::import($import, $file);

        return $this->response->array([
            'model' => $import->model,
            'count' => $import->count,
        ]);
    }

    public function mapToAdvertiser(Request $request)
    {
        $data = $request->get('advertiser');
        dd($data);

        return $this->response->noContent();
    }

}
