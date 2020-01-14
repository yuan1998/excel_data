<?php

namespace App\Clients;

use App\Helpers;
use App\Models\ArchiveType;
use App\Models\ArrivingData;
use App\Models\CustomerPhone;
use App\Models\FormDataPhone;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

class BaseClient
{
    public static $result_data_type = [
        "是否下单"    => 'is_order',
        "是否到院"    => 'is_arriving',
        "网电客户"    => 'temp_visitor',
        "电话"      => 'phone',
        "性别"      => 'gender',
        "建档类型"    => 'archive_type',
        "线上客服"    => 'online_customer',
        "回访次数"    => 'return_visit_count',
        "建档人"     => 'archive_by',
        "回访人"     => 'return_visit_by',
        "客户推荐人"   => 'referrer',
        "建档时间"    => 'archive_date',
        "到院时间"    => 'arriving_date',
        "最后回访时间"  => 'last_return_visit_date',
        "媒介类型"    => 'medium_type',
        "媒介来源"    => 'medium_source',
        "美容院类型"   => 'beauty_salon_type',
        "美容院名称"   => 'beauty_salon_name',
        "美容院"     => 'beauty_salon_name',
        "标签名称"    => 'tag_name',
        "备注"      => 'comment',
        "关注问题"    => 'focus_question',
        "婚姻状况"    => 'marriage',
        "年龄"      => 'age',
        "经济能力"    => 'financial_ability',
        "省份"      => 'province',
        "县市"      => 'county',
        "区"       => 'area',
        "地址"      => 'address',
        "职业"      => 'career',
        "微信号"     => 'weixin',
        "QQ"      => 'qq',
        "预约号"     => 'reservation_number',
        "访客ID"    => 'visitor_id',
        "员工推荐人"   => 'staff_referrer',
        "到院"      => 'is_arriving',
        "下单类型"    => 'order_type',
        "临时客户"    => 'temp_visitor',
        "预约单时间"   => 'reservation_date',
        "意向度"     => 'intention',
        "下单人"     => 'order_by',
        "一级项目"    => 'project_category_1',
        "二级项目"    => 'project_category_2',
        "项目名称"    => 'project_name',
        "下单时间"    => 'order_time',
        "到场时间"    => 'arriving_date',
        "项目备注"    => 'project_remarks',
        "客户备注"    => 'visitor_remarks',
        "临客备注"    => 'temp_remarks',
        "是否成交"    => 'is_transaction',
        "客户状态"    => 'customer_status',
        "二次来院"    => 'again_arriving',
        "客户"      => 'customer',
        "项目需求"    => 'project_demand',
        "网电建档人"   => 'online_archive_by',
        "媒介"      => 'medium',
        "网电回访人"   => 'online_return_visit_by',
        "美学顾问"    => 'site_consultant',
        "医生"      => 'doctor',
        "临客建档时间"  => 'temp_archive_date',
        "接待时间"    => 'reception_date',
        "应付款"     => 'payable',
        "实付款"     => 'real_payment',
        "收费单类型"   => 'order_type',
        "结账时间"    => 'pay_date',
        "接诊单编号"   => 'reception_form_number',
        "预约单编号"   => 'reservation_form_number',
        "收费单编号"   => 'order_form_number',
        "咨询意向度"   => 'intention',
        "科室"      => 'department',
        "预约专家"    => 'reservation_expert',
        "推荐人"     => 'referrer_by',
        "推荐关系"    => 'referrer_relation',
        "客户卡号"    => 'customer_card_number',
        "微信"      => 'weixin',
        "城市"      => 'city',
        "开单"      => 'account_by',
        "收费单号"    => 'order_form_number',
        "客户姓名"    => 'customer',
        "结账日期"    => 'pay_date',
        "总金额"     => 'total',
        "应付金额"    => 'payable',
        "实付金额"    => 'real_payment',
        "开单业绩"    => 'order_account',
        "财务收支总金额" => 'total_pay',
        "返款后业绩"   => 'total_account',
    ];

    public static $arriving_status = [
        '新客户首次' => 2,
        '新客户二次' => 3,
        '老客户二次' => 4,
    ];

    public static $intention_list = [
        0 => '未查询',
        1 => '查不到',
        2 => '一级',
        3 => '二级',
        4 => '三级',
        5 => '四级',
        6 => '五级',
    ];

    public static $arriving_status_list = [
        0 => '未查询',
        1 => '未到院',
        2 => '新客首次',
        3 => '新客户二次',
        4 => '老客户二次',
    ];

    public static $login_url = '/Account/Auth/Login';
    public static $temp_search_url = '/Reservation/TempCustSearch/Index';
    public static $customer_info_check_url = '/Reservation/TempCustInfo/Index';
    public static $reservation_search_url = '/Reservation/ReservationSearch/Index';
    public static $to_hospital_search_url = '/Reservation/ToHospital/Index';
    public static $account_search_url = '/ReportCenter/NetBillAccount/Index';
    public static $customer_phone_check_url = '/CommonArea/CustInfo/ShowPhoneAndLog';
    public static $customer_info_create_url = '/Reservation/TempCustInfo/Create';

    public static $temp_search_result_selector = 'table[data-toggle]';
    public static $reservation_search_result_selector = 'table[data-toggle]';
    public static $to_hospital_search_result_selector = 'table[data-toggle]';

    public static $base_url;
    public static $domain;
    public static $type;
    public static $account;
    public static $cookie_name;
    public static $client;


    /**
     * 获取 Client
     * @return Client
     */
    public static function getClient()
    {
        if (!static::$client) {
            return new Client([
                'base_uri' => static::$base_url,
                'cookies'  => true,
                'defaults' => ['verify' => false],
                'headers'  => [
                    'CLIENT-IP'       => '202.103.229.40',
                    'X-FORWARDED-FOR' => '202.103.229.40',
                    'User-Agent'      => 'Chrome/67.0.3865.90 Safari/507.36'
                ],
                'curl'     => [
                    CURLOPT_REFERER => static::$base_url,
                    CURLOPT_HEADER  => 1,
                ]
            ]);
        }

        return static::$client;
    }


    /**
     * 获取 服务器Token ,判断是否过期,过期则重新获取
     * @return array|mixed
     */
    public static function getToken()
    {
        $key  = static::$type . '_Token_Data';
        $data = json_decode(Redis::get($key), true);

        if (true || !$data || Carbon::now()->gt(Carbon::parse($data['expires']))) {
            $data = static::login();
            Redis::set($key, json_encode($data));
        }

        return $data;
    }

    /**
     * 登录接口,获取新的Token
     * @return array
     */
    public static function login()
    {
        $client = static::getClient();
        $result = $client->post(static::$login_url, [
            'form_params' => static::$account,
        ]);

        $token = $result->getHeader('Set-Cookie');
//        dd($token);
        return Helpers::parseCookie($token);
    }


    /**
     * 获取所有 建档类型
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public static function getArchiveTypes()
    {
        $dom     = static::accountSearchApi([
            'count' => 1
        ]);
        $level_3 = $dom->find('#NetBillAccountIndex-index-select-ztreetree_0')->find('[data-level=3]');

        foreach ($level_3 as $item) {
            ArchiveType::updateOrCreate([
                'title' => $item->text,
            ]);
        }


    }

    /**
     * 查询客户电话APi, 根据ID查询客户电话
     * @param $id
     * @param $type
     * @return string|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws GuzzleException
     * @throws StrictException
     */
    public static function customerPhoneApi($id, $type)
    {
        $data = [
            'id'   => $id,
            'type' => $type
        ];
        $json = static::postUriGetDom(static::$customer_phone_check_url, $data, false);
        preg_match("/\{\"Phone\"\:\"(.*?)\"\}/", $json, $match);

        return isset($match[1]) ? $match[1] : null;
    }

    /**
     * 根据 客户ID 查询对应的电话号码
     * @param        $id
     * @param string $type
     * @return CustomerPhone|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws GuzzleException
     * @throws StrictException
     */
    public static function customerPhoneCreate($id, $type = 'cust_info')
    {
        $phone = static::customerPhoneApi($id, $type);
        if ($phone) {
            return CustomerPhone::updateOrCreate([
                'customer_id'   => $id,
                'customer_type' => $type,
            ], [
                'phone' => $phone
            ]);
        }
        return null;
    }

    /**
     * 临客查询 Api
     * @param      $data
     * @param bool $toDom
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws GuzzleException
     * @throws StrictException
     */
    public static function tempSearchApi($data, $toDom = true)
    {
        $data = array_merge([
            'DatetimeRegStart' => '',
            'DatetimeRegEnd'   => '',
        ], $data ?? []);

        return static::postUriGetDom(static::$temp_search_url, $data, $toDom);
    }

    public static function tempSearchOfDate($start, $end, $count = 10000)
    {
        return static::tempSearchData([
            'DatetimeRegStart' => $start,
            'DatetimeRegEnd'   => $end,
            'pageSize'         => $count,
        ]);
    }

    /**
     * 临客查询 ,获取结果数据
     * @param $data
     * @return \Illuminate\Support\Collection
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public static function tempSearchData($data)
    {
        $dom = static::tempSearchApi($data);

        return static::parserDomTableData($dom);
    }

    /**
     * 临客查询, 查看结果是否为空
     * @param array $data
     * @return bool
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public static function tempSearchExists(array $data)
    {
        return static::tempSearchData($data)->isNotEmpty();
    }


    public static function baiduTempSearch(array $data, $model)
    {
        $item = static::tempSearchData($data)->filter(function ($item) {
            return !!$item['phone'];
        })->first();

        if (!$item) return [];
        return [
            'is_archive'     => 1,
            'has_visitor_id' => !!$item['qq'],
            'has_url'        => !!$item['visitor_id'],
            'archive_type'   => $item['archive_type'],
            'is_repeat'      => Helpers::checkIsRepeat($model->date, $item['archive_date']),
            'turn_weixin'    => Helpers::checkTurnWeixin($item['comment']),
        ];
    }

    /**
     * 预约表单查询 Api 接口
     * @param $data
     * @return Dom
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public static function reservationSearchApi($data)
    {
        $data = array_merge([
            'DatetimeRegStart' => '',
            'DatetimeRegEnd'   => '',
            'DatetimeRegMin'   => '',
            'DatetimeRegMax'   => '',
            'DatetimeReg'      => 1,
            'Isxiangxi'        => 'Y',
        ], $data ?? []);
        return static::postUriGetDom(static::$reservation_search_url, $data);
    }

    /**
     * 预约表单查询 , 获取结果数据
     * @param $data
     * @return \Illuminate\Support\Collection
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public static function reservationSearchData($data)
    {
        $dom = static::reservationSearchApi($data);
        return static::parserDomTableData($dom);
    }

    /**
     * 预约表单查询 , 判断结果是否为空
     * @param $data
     * @return bool
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public static function reservationSearchExists($data)
    {
        return static::reservationSearchData($data)->isNotEmpty();
    }


    /**
     * 预约表单查询, 获取 意向度 1 - 6
     * @param               $data
     * @param FormDataPhone $model
     * @return array
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws GuzzleException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public static function reservationSearchIntention($data, $model): array
    {
        $result = static::reservationSearchData($data)
            ->filter(function ($item) {
                return !!$item['phone'];
            });
        $res    = [
            'intention' => 1,
        ];

        if ($result->isNotEmpty()) {
            $item = $result->first();

            $intention           = Helpers::intentionCheck($item['intention']);
            $res['archive_type'] = $item['archive_type'];
            $res['intention']    = $intention;
            if ($intention > 1) {
                $res['is_archive']  = 1;
                $res['is_repeat']   = Helpers::checkIsRepeat($model->date, $item['archive_date']);
                $res['turn_weixin'] = Helpers::checkTurnWeixin($item['visitor_remarks']);
            }
        }
        return $res;
    }


    /**
     * 到院表单查询,获取 新/老客 和 意向度
     * @param $data
     * @return Dom
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public static function toHospitalSearchApi($data)
    {
        $data = array_merge([
            'DatetimeRegStart'     => '',
            'DatetimeRegEnd'       => '',
            'TempDatetimeRegStart' => '',
            'TempDatetimeRegEnd'   => '',
            'pageSize'             => 1
        ], $data ?? []);

        return static::postUriGetDom(static::$to_hospital_search_url, $data);
    }

    /**
     * 到院数据查询, 请求数据并解析
     * @param $data
     * @return \Illuminate\Support\Collection
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public static function toHospitalSearchData($data)
    {
        $dom = static::toHospitalSearchApi($data);
        return static::parserDomTableData($dom);
    }

    /**
     * 到院数据查询, 查询是否到院,已经到院状态
     * @param $data
     * @return array
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public static function toHospitalSearchArriving($data): array
    {
        $data = static::toHospitalSearchData($data);
        if ($data->isEmpty()) return [
            'arriving_type' => 1,
        ];

        $item      = $data->first();
        $intention = Helpers::intentionCheck($item['intention']);

        $arriving_type = Helpers::arrivingTypeCheck($item);
        return [
            'intention'     => $intention,
            'arriving_type' => $arriving_type,
            'is_archive'    => 1,
        ];
    }

    /**
     * 业绩查询 APi , 返回 HTML dom
     * @param $data
     * @return Dom
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public static function accountSearchApi($data)
    {
        $today = Carbon::now()->toDateString();
        $data  = array_merge([
            'DatetimeCheckoutStart' => $today,
            'DatetimeCheckoutEnd'   => $today,
            'isSearch'              => 1,
            'pageSize'              => 1
        ], $data ?? []);

        return static::postUriGetDom(static::$account_search_url, $data);
    }

    /**
     * 业绩查询 , 获取查询内的业绩数据
     * @param $data
     * @return \Illuminate\Support\Collection
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public static function accountSearchData($data)
    {
        $dom = static::accountSearchApi($data);
        return static::parserDomTableData($dom);
    }

    public static function tempCustomerInfoCreateApi($data)
    {
        $date = Carbon::now()->toDateTimeString();
        $data = array_merge([
            'DatetimeReg'    => $date,
            'CustName'       => '自动创建',
            'Province'       => '610000',
            'Age'            => '1',
            'Sex'            => '1',
            'City'           => '610100',
            'Country'        => '0',
            'IncomeCapacity' => 'IC_CHA',
        ], $data);
        return static::postUriGetDom(static::$customer_info_create_url, $data, false);
    }

    public static function createCustomerInfo($data)
    {
        if (!isset($data['phone'])) {
            throw new \Exception('电话不能为空.');
        }
        if (!isset($data['TmpCustRegType'])) {
            throw new \Exception('建档类型不能为空.');
        }
        if (!isset($data['CreatedBy'])) {
            throw new \Exception('建档人不能为空.');
        }
        if (!isset($data['PlanRecallEmps'])) {
            throw new \Exception('回访人不能为空.');
        }
        if (!isset($data['MediaSource'])) {
            throw new \Exception('媒介不能为空.');
        }
        if (!isset($data['MediaSourceType'])) {
            throw new \Exception('媒介类型不能为空.');
        }

        $data['TmpCustRegType'] = Arr::get(Helpers::$ArchiveTypeCode, $data['TmpCustRegType'], null);

        if (!$data['TmpCustRegType']) {
            throw new \Exception('错误的建档类型.');
        }

        $data['CreatedBy'] = Arr::get(Helpers::$UserIdCode, $data['CreatedBy'], null);

        if (!$data['CreatedBy']) {
            throw new \Exception('错误的建档人.');
        }

        $data['MediaSource'] = Arr::get(Helpers::$MediumSourceCode, $data['MediaSource'], null);

        if (!$data['MediaSource']) {
            throw new \Exception('错误的媒介.');
        }
        $data['MediaSourceType'] = Arr::get(Helpers::$MediumSourceTypeCode, $data['MediaSourceType'], null);

        if (!$data['MediaSourceType']) {
            throw new \Exception('错误的媒介类型.');
        }
        $data['PlanRecallEmp'] = Arr::get(Helpers::$UserIdCode, $data['PlanRecallEmps'], null);

        if (!$data['PlanRecallEmp']) {
            throw new \Exception('错误的回访人.');
        }

        return static::tempCustomerInfoCreateApi($data);
    }

    public static function tempCustomerInfoCheckApi($phone)
    {
        $data = array_merge([
            'pageCurrent' => 1,
        ], ['Phone' => $phone]);
        return static::postUriGetDom(static::$customer_info_check_url, $data);
    }


    public static function tempCustomerInfoCheckData($phone)
    {
        $dom = static::tempCustomerInfoCheckApi($phone);
        return static::parserDomTableData($dom, 'table.table-hover');
    }

    public static function tempCustomerInfoCheckExists($phone)
    {
        $dom = static::tempCustomerInfoCheckApi($phone);
        return static::parserDomTableData($dom, 'table.table-hover')->isNotEmpty();
    }

    public static function tempCustomerInfoArchiveCheck($model)
    {
        $item = static::tempCustomerInfoCheckData($model->phone)->first();
        if (!$item) return [
            'is_archive' => 2,
        ];

        return [
            'is_archive' => 1,
            'intention'  => Helpers::intentionCheck($item['intention']),
            'is_repeat'  => 2,
        ];
    }


    /**
     * 解析HTML Dom to Array Data
     * @param      $dom
     * @param null $select
     * @return \Illuminate\Support\Collection
     */
    public static function parserDomTableData($dom, $select = null)
    {
        $select = $select ?? static::$to_hospital_search_result_selector;

        $dataList = $dom->find($select);
        $data     = collect(Helpers::parserHtmlTable($dataList, static::$result_data_type));
        return $data;
    }

    /**
     * @param $uri
     * @param $data
     * @param $toDom
     * @return Dom|string
     * @throws GuzzleException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws StrictException
     */
    public static function postUriGetDom($uri, $data, $toDom = true)
    {
        $cookies = static::getToken();
        $client  = static::getClient();

        $jar = CookieJar::fromArray($cookies, static::$domain);

        $result = $client->request('POST', $uri, [
            'form_params' => $data,
            'cookies'     => $jar, 'timeout' => 0,
        ]);

        $body = $result->getBody()->getContents();

        if ($toDom) {
            $dom = new Dom;
            $dom->load($body);
            return $dom;
        }
        return $body;
    }

}

