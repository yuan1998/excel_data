<?php

namespace App\Http\Controllers\Api;


use App\Clients\KqClient;
use App\Clients\YiliaoClient;
use App\Clients\ZxClient;
use App\Encoding;
use App\Exports\TestExport;
use App\Helpers;
use App\Http\Requests\UploadRequest;
use App\Imports\BaiduImport;
use App\Imports\BaiduSpendImport;
use App\Imports\OppoSpendImport;
use App\Imports\WeiboFormDataImport;
use App\Imports\YiliaoImport;
use App\Models\ArchiveType;
use App\Models\ArrivingData;
use App\Models\BaiduClue;
use App\Models\BaiduData;
use App\Models\BillAccountData;
use App\Models\CrmUser;
use App\Models\FormDataPhone;
use App\Models\ProjectType;
use App\Models\SpendData;
use App\Models\TempCustomerData;
use App\Models\WeiboFormData;
use App\Parsers\ParserStart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class BaiduDataController extends Controller
{
    public $modelName = '\\App\\Models\\BaiduData';

    public function saveRequest(Request $request)
    {
        $data = $request->all();
        $json = json_encode($data);
        $key  = md5($json);
        Redis::set($key, $json);
        return $this->response->array([
            'key' => $key
        ]);
    }

    public function uploadExcel(UploadRequest $request)
    {
        $type = $request->get('type');
        Excel::import(new BaiduImport(), $request->file('excel'));

        return $this->response->noContent();
    }


    public function test(Request $request)
    {
        $file = $request->file('excel');
        Helpers::checkUTF8($file);

        $import = new OppoSpendImport();
        Excel::import($import, $file);

//        $date1  = "2019-12-1 00:00:00";
//        $date2  = "2019-12-2 00:00:00";
//        $result = YiliaoClient::getYiliaoData($date1, $date2);
        dd($import);

        return $this->response->noContent();
    }
}
