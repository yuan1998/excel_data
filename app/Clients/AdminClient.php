<?php

namespace App\Clients;

use App\Exports\ReportExport;
use App\Helpers;
use Maatwebsite\Excel\Excel;

class AdminClient extends BaseClient
{
    public static $type = 'zx';
    public static $cookie_name = '172.16.8.880_AdminContext_';
    public static $base_url = 'http://172.16.8.8/';
    public static $domain = '172.16.8.8';
    public static $companyApi = false;
    public static $mediaSourceType = '9295C7B6F93E4E51A9C09E1C2198CCB5';

//    public static $account_search_url = '/ReportCenter/NetBillAccountDtl/CareIndex';

    public static $url = 'http://172.16.8.8:8080/bmsprd/reportJsp/queryReport.jsp?rpx=oneSectionLevel1.rpx&scroll=no&';

    public static $account = [
        'username' => '999',
        'password' => 'wmx1120',
    ];

    public static $baseAccount = [
        'username' => '7010',
        'password' => 'hm2018',
    ];


    public static function test()
    {
        $result = collect();
        Helpers::dateRangeForEach(['2020-04-01', '2020-09-30'], function ($date) use (&$result) {
            $dateString = $date->toDateString();
            $data       = static::getReportOfDate($dateString)->map(function ($item) use ($dateString) {
                $item['日期'] = $dateString;
                return $item;
            });
            $result     = $result->merge($data);
        });

        $export = new ReportExport($result);

        \Maatwebsite\Excel\Facades\Excel::store($export, 'test_excel/test.xlsx', 'public');

    }


    public static function getReportOfDate($date)
    {
        $data = [
            "form"            => "oneSectionLevel1_arg.rpx",
            "orgid"           => "100",
            "sdate"           => $date,
            "scale"           => "100",
            "needFunctionBar" => "0",
            "action"          => "8",
            "border"          => "border:1px solid blue",
            "needQuery"       => "1",
            "userid"          => "1831D2D5623A4F499E6B71246618FAB5",
            "edate"           => $date,
            "isredirect"      => "Y",
            "rpx"             => "oneSectionLevel1.rpx",
            "w"               => "1666",
            "section"         => "PLASTIC",
            "isquery"         => "Y",
        ];

        return static::getReportExcel($data);
    }

    public static function getReportExcel($data)
    {
        $response = static::postUriGetDom(static::$url, $data);


        $keysHtml = $response->find('table[id=report1_$_top] tr[rn=3]')->find('td');

        $keys = [];
        foreach ($keysHtml as $key) {
            $keys[] = trim(strip_tags($key->innerHTML));
        }

        $valueHtml = $response->find('table[id=report1] tr');
        $result    = [];

        foreach ($valueHtml as $rowIndex => $value) {
            foreach ($value->find('td') as $colIndex => $td) {
                $rowSpan    = (int)($td->getAttribute('rowspan') ?? 1);
                $colSpan    = (int)($td->getAttribute('colspan') ?? 1);
                $colContext = $td->innerHTML;

                try {
                    if ($aTag = $td->find('a')) $colContext = $aTag->innerHTML;
                } catch (\Exception $exception) {
                }

                while (isset($result[$rowIndex]) && isset($result[$rowIndex][$keys[$colIndex]]))
                    $colIndex++;

                $rowRange = $rowIndex + $rowSpan;
                $colRange = $colIndex + $colSpan;
                for ($rowI = $rowIndex; $rowI < $rowRange; $rowI++) {
                    for ($colI = $colIndex; $colI < $colRange; $colI++) {
                        if (!isset($result[$rowI])) $result[$rowI] = [];

                        $key                 = $keys[$colI];
                        $result[$rowI][$key] = trim(strip_tags($colContext));
                    }
                }
            }

        }

        $result = collect($result)
            ->filter(function ($item) {
                return !preg_match("/总合计/", $item['二级分类']);
            });

        return $result;
    }
}
