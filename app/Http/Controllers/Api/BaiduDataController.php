<?php

namespace App\Http\Controllers\Api;


use App\Clients\KqClient;
use App\Clients\ZxClient;
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
use App\Models\TempCustomerData;
use App\Models\WeiboFormData;
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

    /*


     */

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    public function test(Request $request)
    {

        $item = WeiboFormData::find(195);

        dd($item->dispatchItem());

        Excel::import(new WeiboFormDataImport(), $request->file('excel'));
        dd();

//        $result = TempCustomerData::getToday('kq' , false);
//        dd($result);
//        $count = BillAccountData::generateBillAccountOfDate('kq', '2019-10-01', '2019-10-10');
//        $count = ArrivingData::arrivingDataGenerateOfDate('kq', '2019-10-01', '2019-10-10');
//        return $this->response->array([$count]);
//        return $this->response->array([$count]);
        KqClient::getArchiveTypes();
        return $this->response->noContent();

        $dom = KqClient::toHospitalSearchData([
            'DatetimeRegStart' => '2019-10-07',
            'DatetimeRegEnd'   => '2019-10-07',
            'pageSize'         => 2
        ]);
        return $this->response->array($dom->toArray());
    }
}
