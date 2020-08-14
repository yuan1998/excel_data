<?php

namespace App\Http\Controllers\Api;


use App\Clients\KqClient;
use App\Clients\SfClient;
use App\Clients\YiliaoClient;
use App\Clients\ZxClient;
use App\Encoding;
use App\Exports\BaiduPlanExport;
use App\Exports\ConsultantGroupExport;
use App\Exports\TestExport;
use App\Helpers;
use App\Http\Requests\UploadRequest;
use App\Imports\AutoImport;
use App\Imports\BaiduImport;
use App\Imports\BaiduSpendImport;
use App\Imports\OppoSpendImport;
use App\Imports\WeiboFormDataImport;
use App\Imports\YiliaoImport;
use App\Models\AccountData;
use App\Models\ArchiveType;
use App\Models\ArrivingData;
use App\Models\BaiduClue;
use App\Models\BaiduData;
use App\Models\BillAccountData;
use App\Models\CrmUser;
use App\Models\ExportDataLog;
use App\Models\FormDataPhone;
use App\Models\ProjectType;
use App\Models\SpendData;
use App\Models\TempCustomerData;
use App\Models\WeiboFormData;
use App\Parsers\BaiduPlanData;
use App\Parsers\ParserConsultantGroup;
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


    /**
     * 导出 百度信息流计划分析表
     * 参数:  type  , 用于获取所属 type 下所有账户
     * 参数:  dates , 用于筛选日期范围
     *
     * 逻辑总结:
     * 1, 使用[type] 获取所属 [Account]
     * 2, 用 [Account] 和 [dates] 来获取关联的 [FormData] 和 [SpendData]
     * 3, 按照日期将 [FormData] 和 [SpendData] 分组
     * 4, 分别将每个日期中 的 每个 [SpendData] 进行判断 [计划名(plan_name)] 是否匹配正则 /\-(d{6})/ (横杆开头 带有6位日期数字)
     * 5, 将匹配中的 [计划名(plan_name)] 匹配与日期对应的 [FormData], 分别将每个数据中的 [dialog_url(对话url进行匹配)]
     * 6, 最后数据格式
     *    [
     *        'account' => [
     *               'date' => [
     *                     'plan_name' => [
     *                         'code' => 'xaaaaa-200101',
     *                         'account_name'=> 'xaaaaa',
     *                         'title' => 'xxxxxxxxxx-200101',
     *                         'spendData' => [....]
     *                         'formData' => [....]
     *                     ]
     *               ]
     *         ]
     *
     *    ]
     * 7, 转换数据
     * 8, 导出数据表
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function accountPlanExcelExport(Request $request)
    {
        $data = $request->only(['dates', 'type']);

        $data['data_type'] = 'baidu_plan';

        $exportDataLog = ExportDataLog::generate($data);
        if (!$exportDataLog) {
            $this->response->errorBadRequest();
        }

        return $this->response->array([0]);
    }

    public function testSanfang()
    {
        $client = new SfClient('2020-07-01', '2020-07-08');

        $client->makeExcel();
    }


    public function testBaseExcel()
    {
        $data = [
            "consultant_group_id" => "",
            "data_type"           => 'xxl_data_excel',
            "department_id"       => [
                1, 3, 4
            ],
            "channel_id"          => [
                "1", "4",
            ],
            "type"                => "zx",
            "dates"               => [
                "2020-07-01 00:00:00",
                "2020-07-2 23:59:59",
            ],
        ];

        $parser = new ParserStart($data);
        $export = new TestExport($parser);

        Excel::store($export, 'test_excel/test.xlsx', 'public');

    }

    public function testPhone()
    {
        $model = FormDataPhone::find(3531);
        Helpers::checkIntentionAndArchive($model);

//        $result = ZxClient::tempCustomerInfoCheckData("17791229833");
        dd($model->toArray());
    }

    public function testIP()
    {
        $ip = request()->ip();
        dd($ip);
    }

    public function test(Request $request)
    {
        $this->testIP();
//        $this->testSanfang();
//        $this->testBaseExcel();
//        $this->testPhone();
//        $type = 'kq';
//        dd($type);
//        return TempCustomerData::getDataOfDate($type, '2020-06-27', '2020-06-27');

//        $file = $request->file('excel');
//        Helpers::checkUTF8($file);


//        dd($data->toArray(), $mediumId, $dates);


//        $date1  = "2019-12-1 00:00:00";
//        $date2  = "2019-12-2 00:00:00";
//        $result = YiliaoClient::getYiliaoData($date1, $date2);
//        dd($import);

        return $this->response->noContent();
    }
}
