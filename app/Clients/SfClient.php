<?php

namespace App\Clients;

use App\Exports\SfExport;
use App\Exports\SfSheet;
use App\Exports\TestExport;
use App\Helpers;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\CustomerPhone;
use App\Models\MediumType;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;

class SfClient extends BaseClient
{
    public static $type = 'zx';
    public static $cookie_name = '172.16.8.280_AdminContext_';
    public static $base_url = 'http://172.16.8.2/';
    public static $domain = '172.16.8.2';
//    public static $companyApi = true;
    public static $mediaSourceType = 'E10D9F6497004F29BE8DA99600EF5B1D';

    public static $account = [
        'username' => '6003',
        'password' => '666888',
    ];

    public $startDate = '';
    public $endDate = '';

    public $dayLogs = [];
    public $result = [];
    /**
     * @var \Illuminate\Support\Collection
     */
    public $hospitalData;
    /**
     * @var \Illuminate\Support\Collection
     */
    public $accountData;
    public $mediumIds;
    public $total = 0;

    /**
     * SfClient constructor.
     * @param $start
     * @param $end
     */
    public function __construct($start, $end)
    {
        $this->startDate = $start;
        $this->endDate   = $end;

        var_dump('初始化');

//        $this->hospitalData = $this->toHospitalData($this->startDate, $this->endDate);
//        $this->accountData = $this->getAccountSearchData($this->startDate, $this->endDate);
    }

    public function getArrivingData($date)
    {
//        $ids = $this->getMediumIds();

        return ArrivingData::query()
            ->with(['customerPhone'])
            ->where('medium', 'like', '%三方转诊%')
            ->where('type', 'zx')
            ->whereDate('reception_date', $date)
            ->get();
    }

    /*
    > install_name_tool -change
    /usr/local/Cellar/python3/3.6.3/Frameworks/Python.framework/Versions/3.6/Python
    /usr/local/Cellar/python/3.6.5/Frameworks/Python.framework/Versions/3.6/Python /usr/local/bin/python



     */
    public function getBillAccountData($date)
    {
        return BillAccountData::query()
            ->with(['customerPhone'])
            ->where('medium', 'like', '%三方转诊%')
            ->where('type', 'zx')
            ->whereDate('pay_date', $date)
            ->get();
    }

    public function getMediumIds()
    {
        if (!$this->mediumIds) {
            $this->mediumIds = MediumType::query()
                ->select(['id', 'title'])
                ->where('title', 'like', '%三方转诊%')
                ->get()
//            dd($this->mediumIds->toArray());
                ->pluck('id');

        }

        return $this->mediumIds;
    }

    public function toHospitalData($start, $end)
    {

        $data = [
            'DatetimeRegStart' => $start,
            'DatetimeRegEnd'   => $end,
            "pageSize"         => 1000
        ];
        return static::toHospitalSearchData($data)->sortByDesc(function ($item) {
            return preg_match('/是/', $item['is_transaction']);
        });
    }

    public function getAccountSearchData($start, $end)
    {
        $data = [
            'DatetimeCheckoutStart'    => $start,
            'DatetimeCheckoutEnd'      => $end,
            "TypeId1"                  => '',
            "TypeId2"                  => '',
            "TypeId3"                  => '',
            "ProductName"              => '',
            "ProductSuitName"          => '',
            "TmpCustRegType"           => '',
            "TmpCustRegTypeMenus"      => '',
            "MediaSourceType"          => '',
            "MediaSource"              => '',
            "ChargeTypes"              => '',
            "DatetimeRegStart"         => '',
            "DatetimeRegEnd"           => '',
            "MinDatetimeCheckoutStart" => '',
            "MinDatetimeCheckoutEnd"   => '',
            "CustName"                 => '',
            "Phone"                    => '',
            "CustCardNo"               => '',
            "IsHospSecond"             => '',
            "CustStatus"               => '',
            "SalesConsultantId"        => '',
            "CreatedBy"                => '',
            "PlanRecallEmpname"        => '',
            "ChargeNo"                 => '',
            "RealPaymentStart"         => '',
            "RealPaymentEnd"           => '',
            "GuestId"                  => '',
            "pageSize"                 => '500',
            "pageCurrent"              => '1',
            "orderField"               => '',
            "orderDirection"           => '',
            "total"                    => '',
        ];
        return static::accountSearchData($data);
    }

    public function makeExcel()
    {
        $this->main();
//        dd($this->result);


        return new SfExport($this->result, '三方数据', [
            '到院时间', '是否成交', '客户状态', '客户姓名', '电话', '跟进客服', '媒介', '现场咨询', '客户卡号', '建档项目', '成交项目', '消费金额', '备注'
        ]);

    }

    public function mergeAccountData($hospitalData, $accountData)
    {
        $result = [];

        foreach ($hospitalData as $hospitalItem) {
            $hospitalItem = $hospitalItem->toArray();
            $id           = $hospitalItem['customer_id'];
            if (!Arr::exists($result, $id)) {
                $result[$id] = $this->mapToMergeHospitalItem($hospitalItem);
            }
        }

        foreach ($accountData as $accountItem) {
            $accountItem = $accountItem->toArray();
            $id          = $accountItem['customer_id'];
            if (!Arr::exists($result, $id)) {
                $result[$id] = $this->mapToMergeAccountItem($accountItem);
            }

            if (!Arr::exists($result[$id], 'account_data')) {
                $result[$id]['account_data'] = [];
            }

            if (!Arr::exists($result[$id]['account_data'], $accountItem['order_type'])) {
                $result[$id]['account_data'][$accountItem['order_type']] = 0;
            }

            $result[$id]['account_data'][$accountItem['order_type']] += (float)$accountItem['order_account'];
        }
        return $result;
    }

    public function mapToMergeHospitalItem($value)
    {
        if (preg_match('/新客户/', $value['customer_status'])) {
            if (preg_match('/二次/', $value['again_arriving'])) {
                $value['customer_status'] = '新客二次';
            } else {
                $value['customer_status'] = '新客首次';
            }
        } else {
            $value['customer_status'] = '老客';
        }

        return $value;
    }

    public function mapToMergeAccountItem($item)
    {
        $item['reception_date']  = Carbon::parse($item['pay_date'])->toDateString();
        $item['is_transaction']  = '是';
        $item['customer_status'] = '老客';
        return $item;
    }

    public function main()
    {
        var_dump('开始获取数据');

        Helpers::dateRangeForEach([$this->startDate, $this->endDate], function ($date) {
            $day = $date->toDateString();


            $hospitalData = $this->getArrivingData($day);

            $accountData = $this->getBillAccountData($day);

            $data = $this->mergeAccountData($hospitalData, $accountData);

            $this->makeResult(collect($data), $day);

        });

    }

    public function makeResult($arr, $day)
    {
        var_dump('开始处理 ' . $day . ' 的数据');
        $arrCount    = $arr->count();
        $arrComplete = 0;

        foreach ($arr as $item) {
            $arrComplete++;
            var_dump('数据进行中 : ' . $arrCount . ' / ' . $arrComplete . ' .');


            $tmpItem = $this->generateItemTemp($item);
            $id = $item['customer_id'];

            if (!Arr::exists($item, 'account_data')) {
                $this->saveResultItem($day, $tmpItem);
            } else {
                foreach ($item['account_data'] as $accountName => $accountValue) {
                    if (preg_match('/正常收费单/', $accountName)) {
                        $this->generateNormalItem($day, $id, $tmpItem, $accountName, $accountValue);
                    } else if (preg_match('/辅助治疗/', $accountName)) {
                        $this->generateNormalItem($day, $id, $tmpItem, $accountName, $accountValue, false);
                    } else {
                        $this->saveResultItem($day, $tmpItem, [
                            'project' => $accountName,
                            'account' => $accountValue,
                        ]);
                    }
                }
            }

        }

        var_dump($day . ' 的数据处理完成.');
    }

    public function saveResultItem($day, $item, $data = null)
    {
        if (is_array($data)) {
            $item = array_merge($item, $data);
        }
        array_push($this->result, $item);
    }

    public function generateItemTemp($item)
    {
        $id    = $item['customer_id'];
        $phone = CustomerPhone::firstOrCreate([
            'customer_id'   => $item['customer_id'],
            'type'          => $item['type'],
            'customer_type' => CustomerPhone::$customerType,
        ], [
            'client' => $item['client'],
        ]);


        if (!$phone['phone']) {
            $phoneNumber = $phone->checkPhone();
        } else {
            $phoneNumber = $phone['phone'];
        }

        $info = $this->custInfo($id);

        return [
            'reception_date'       => $item['reception_date'],
            'is_transaction'       => $item['is_transaction'],
            'customer_status'      => $item['customer_status'],
            'customer'             => $item['customer'],
            'phone'                => $phoneNumber ?? '无',
            'online_customer'      => $item['online_customer'],
            'medium'               => $item['medium'],
            'down'                 => $info,
            'customer_card_number' => $item['customer_card_number'],
            'archive_type'         => $item['archive_type'],
            'project'              => '未成交',
            'account'              => 0,
            'comment'              => '无',
        ];
    }

    public function generateNormalItem($day, $id, $item, $name, $accountValue, $a = true)
    {
        $projectName = $this->payInfo($id, $day, $a);
        $name        = $name . ' : ' . $projectName;

        $this->saveResultItem($day, $item, [
            'project' => $name,
            'account' => $accountValue,
        ]);
    }


    public static function checkPhone($customer_id)
    {
        $phoneList = static::baseCustomerInfoApi($customer_id, static::$cust_info_cust_infos_url);

        return $phoneList;
    }

    public static function payInfo($id, $day, $a = true)
    {
        $charge = static::normalChargeData($id, $a);
        $data   = $charge->filter(function ($item) use ($day) {
            return Carbon::parse($item['pay_date'])->toDateString() == $day;
        });

        $project = [];
        foreach ($data as $item) {
            $project[] = $item['产品名称'];
        }

        return implode(' + ', $project);
    }

    public static function storeInfo($id, $day)
    {
        $storeData = static::customerPreChargeData($id)->filter(function ($item) use ($day) {
            return Carbon::parse($item['pay_date'])->toDateString() == $day;
        });

        if ($storeData->isEmpty()) return false;

        $store = 0;

        foreach ($storeData as $item) {
            $store += (float)$item['金额'];
        }

        return $store;
    }

    public function custInfo($id)
    {
        $info = static::custInfoApi($id);
        preg_match("/美学顾问：(.*?) /", $info, $match);

        return $match ? $match[1] : '未知';
    }


    public static function test()
    {

        $start = '2020-10-01';
        $end = '2020-10-01';
//        ArrivingData::getDataOfDate('sf', $start, $end);
//        BillAccountData::getDataOfDate('sf', $start, $end);
        $client = new static($start, $end);
        $client->main();
    }
}

