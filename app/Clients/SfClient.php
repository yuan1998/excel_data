<?php

namespace App\Clients;

use App\Exports\SfExport;
use App\Exports\SfSheet;
use App\Exports\TestExport;
use App\Helpers;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;

class SfClient extends BaseClient
{
    public static $type = 'zx';
    public static $cookie_name = '172.16.8.880_AdminContext_';
    public static $base_url = 'http://172.16.8.8/';
    public static $domain = '172.16.8.8';

    public static $account = [
        'username' => '6003',
        'password' => '1',
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

    public function toHospitalData($start, $end)
    {

        $data = [
            'DatetimeRegStart' => $start,
            'DatetimeRegEnd'   => $end,
            "pageSize"         => 1000
        ];
        return static::toHospitalSearchData($data)->sortByDesc(function ($item) {
            return $item['is_transaction'] == ' 是 ';
        });
    }

    public function getAccountSearchData($start, $end)
    {
        $data = [
            'DatetimeCheckoutStart' => $start,
            'DatetimeCheckoutEnd'   => $end,
            'pageSize'              => 1000,
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


    public function generateExcelFile()
    {
        $export   = $this->makeExport();
        $filename = '三方数据_' . $this->startDate . '_' . $this->endDate . '.xlsx';

        Excel::store($export, 'test_excel/' . $filename, 'public');
    }


    public function mergeAccountData($hospitalData, $accountData)
    {
        $result = [];

        foreach ($hospitalData as $hospitalItem) {
            $id = $hospitalItem['customer_id'];
            if (!Arr::exists($result, $id)) {
                $result[$id] = $this->mapToMergeHospitalItem($hospitalItem);
            }
        }

        foreach ($accountData as $accountItem) {
            $id = $accountItem['customer_id'];
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
        if ($value['customer_status'] == ' 新客户 ') {
            if ($value['again_arriving'] == '二次') {
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

            var_dump('获取 ' . $day . ' 的数据:开始');

            var_dump('获取 ' . $day . ' 的数据: 获取业绩数据 .... ');
            $accountData = $this->getAccountSearchData($day, $day);
            var_dump('获取 ' . $day . ' 的数据: 获取到院数据 .... ');
            $hospitalData = $this->toHospitalData($day, $day);

            $data = $this->mergeAccountData($hospitalData, $accountData);

            var_dump('获取 ' . $day . ' 的数据:成功.');

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
            $id      = $item['customer_id'];

            if (!Arr::exists($item, 'account_data')) {
                $this->saveResultItem($day, $tmpItem);
            } else {
                foreach ($item['account_data'] as $accountName => $accountValue) {
                    if ($accountName === ' 正常收费单 ') {
                        $this->generateNormalItem($day, $id, $tmpItem, $accountName, $accountValue);
                    } else if ($accountName === ' 辅助治疗 ') {
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
        $phone = $this->checkPhone($id);
        $info  = $this->custInfo($id);

        return [
            'reception_date'       => $item['reception_date'],
            'is_transaction'       => $item['is_transaction'],
            'customer_status'      => $item['customer_status'],
            'customer'             => $item['customer'],
            'phone'                => Arr::get($phone, 0, '无'),
            'online_customer'      => $item['online_customer'],
            'medium'               => $item['medium'],
            'down'                 => $info,
            'customer_card_number' => $item['customer_card_number'],
            'archive_type'         => $item['archive_type'],
            'project'              => '未成交',
            'account'              => 0,
            'comment'              => Arr::get($phone, 1, '无'),
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
        $phoneList   = static::baseCustomerInfoApi($customer_id);
        $phoneResult = [];
        foreach ($phoneList as $phone) {
            $phoneResult[] = static::customerPhoneApi($phone['id'], $phone['type']);

        }

        return $phoneResult;
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

}

