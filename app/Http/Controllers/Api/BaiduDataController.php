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

"""
HTTP/1.1 200 OK
Cache-Control: private
Content-Type: application/json; charset=utf-8
Server: Microsoft-IIS/8.5
X-AspNetMvc-Version: 4.0
X-AspNet-Version: 4.0.30319
X-Powered-By: ASP.NET
Date: Tue, 29 Oct 2019 07:23:33 GMT
Content-Length: 243

{"statusCode":"200","message":"添加成功","closeCurrent":true,"tabid":"ReservationTempCustInfoIndex","forward":"/Reservation/TempCustInfo/CreateTempNote?phone=13192567990E\u0026\u0026id=EC6616FA31CB4269B661AAF500FDAB12","forwardConfirm":""}
"""

"""
HTTP/1.1 200 OK
Cache-Control: private
Transfer-Encoding: chunked
Content-Type: application/json; charset=utf-8
Server: Microsoft-IIS/8.5
X-AspNetMvc-Version: 4.0
X-AspNet-Version: 4.0.30319
X-Powered-By: ASP.NET
Date: Tue, 29 Oct 2019 07:25:08 GMT

{"statusCode":"300","message":"\u003cb\u003e系统发生异常，请联系系统管理员。\u003c/b\u003e\u003c/p\u003e\u003cb\u003e异常消息:  \u003c/b\u003eORA-00001: 违反唯一约束条件 (BMSKQ.UQ_TEMP_CUST_INFO_ID_PHONE)\u003c/p\u003e\u003cb\u003e触发Action:  \u003c/b\u003eCreate\u003c/p\u003e\u003cb\u003e异常类型:  \u003c/b\u003eOracle.ManagedDataAccess.Client.OracleException"}
"""

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
