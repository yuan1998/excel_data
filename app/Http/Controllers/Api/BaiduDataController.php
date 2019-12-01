<?php

namespace App\Http\Controllers\Api;


use App\Clients\KqClient;
use App\Clients\ZxClient;
use App\Exports\TestExport;
use App\Helpers;
use App\Http\Requests\UploadRequest;
use App\Imports\BaiduImport;
use App\Imports\WeiboFormDataImport;
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
        $item = SpendData::find(2817);
        Helpers::formDataCheckAccount($item, 'account_keyword', 'spend_type', true);
        return $this->response->noContent();


        $requestData = [
            'channel_id'    => [
                3
            ],
            'department_id' => [
                1, 3, 4
            ],
            'dates'         => [
                '2019-11-01',
                '2019-11-30'
            ]
        ];


        $parser = new ParserStart($requestData);
        $parser->toArray('channel');
        $pathName = 'test_excel/test.xlsx';
        Excel::store(new TestExport($parser), $pathName, 'public');

        return $this->response->noContent();
    }
}
