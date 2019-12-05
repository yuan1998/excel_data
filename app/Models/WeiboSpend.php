<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class WeiboSpend extends Model
{
    public static $excelFields = [
        "时间"        => 'date',
        "广告账号"      => 'advertiser_account',
        "广告计划"      => 'advertiser_plan',
        "投放场景"      => 'delivery_scenario',
        "投放目标"      => 'target',
        "曝光量"       => 'show',
        "曝光量_粉丝"    => 'show_fans',
        "花费"        => 'spend',
        "花费_粉丝"     => 'spend_fans',
        "千次曝光成本"    => 'thousand_times_show_price',
        "千次曝光成本_粉丝" => 'thousand_times_show_price_fans',
        "评论"        => 'comment_count',
        "评论_粉丝"     => 'comment_count_fans',
        "加关注数"      => 'follow_count',
        "加关注数_粉丝"   => 'follow_count_fans',
        "互动数"       => 'interactive',
        "互动数_粉丝"    => 'interactive_fans',
        "导流数"       => 'diversions',
        "导流数_粉丝"    => 'diversions_fans',
        "互动率"       => 'interactive_rate',
        "互动率_粉丝"    => 'interactive_rate_fans',
        "表单提交"      => 'form_count',
        "表单提交_粉丝"   => 'form_count_fans',
        "点赞"        => 'like_count',
        "点赞_粉丝"     => 'like_count_fans',
        "转发"        => 'share_count',
        "转发_粉丝"     => 'share_count_fans',
        "收藏"        => 'start_count',
        "收藏_粉丝"     => 'start_count_fans',
        "单次互动成本"    => 'once_interactive_price',
        "单次互动成本_粉丝" => 'once_interactive_price_fans',
        "质量分"       => 'quality_score',
        "质量分_粉丝"    => 'quality_score_fans',
        "负面指数"      => 'negative',
        "负面指数_粉丝"   => 'negative_fans',

    ];

    protected $fillable = [
        'type',
        'date',
        'advertiser_account',
        'advertiser_plan',
        'show',
        'show_fans',
        'interactive',
        'interactive_fans',
        'interactive_rate',
        'interactive_rate_fans',
        'spend',
        'spend_fans',
        'thousand_times_show_price',
        'thousand_times_show_price_fans',
        'once_interactive_price',
        'once_interactive_price_fans',
        'quality_score',
        'quality_score_fans',
        'negative',
        'negative_fans',
    ];

    /**
     * @param ArrivingData|BillAccountData|TempCustomerData $value
     * @return null
     */
    public static function getWeiboType($value)
    {
        if ($value) {
            $visitorId = data_get($value, 'visitor_id', null);

            if (preg_match("/评论/", $visitorId)) {
                return 'comment';
            }
            if (preg_match("/表单/", $visitorId)) {
                return 'form';
            }
            if (preg_match("/私信/", $visitorId)) {
                return 'message';
            }
            if (preg_match("/关注/", $visitorId)) {
                return 'follow';
            }

            return 'other';
        }

        return null;
    }

    /**
     * @param Collection $data
     * @return bool
     */
    public static function isModel($data)
    {
        $keys  = array_keys(static::$excelFields);
        $first = $data->get(0);
        $diff  = $first->diff($keys);
        $count = $diff->count();
        return $count <= 2;
    }


    /**
     * @param Collection $collection
     * @return int
     * @throws \Exception
     */
    public static function excelCollection($collection)
    {
        $data = Helpers::excelToKeyArray($collection, WeiboSpend::$excelFields);

        $data = collect($data)->filter(function ($item) {
            return isset($item['date'])
                && isset($item['advertiser_account'])
                && isset($item['diversions'])
                && isset($item['follow_count'])
                && $item['advertiser_account'] != '-';
        });

        return static::handleExcelData($data);
    }

    public static function handleExcelData($data)
    {
        $count = 0;
        foreach ($data as $item) {
            $item  = static::parseData($item);
            $weibo = WeiboSpend::updateOrCreate([
                'date'               => $item['date'],
                'advertiser_account' => $item['advertiser_account'],
            ], $item);

            $spend = SpendData::updateOrCreate([
                'model_id'   => $weibo->id,
                'model_type' => WeiboSpend::class
            ], SpendData::parseWeiboMakeSpendData($item));
            $spend->projects()->sync($item['project_type']);

            $count++;
        }

        return $count;
    }


    /**
     * @param $item
     * @return mixed
     * @throws \Exception
     */
    public static function parseData($item)
    {
        $code = $item['code'] = $item['advertiser_account'];

        if (!$departmentType = Helpers::checkDepartment($code)) {
            throw new \Exception('无法判断科室:' . $code);
        }
        $item['type'] = $departmentType->type;;
        $item['department_id']   = $departmentType->id;
        $item['department_type'] = $departmentType;
        $item['project_type']    = Helpers::checkDepartmentProject($departmentType, $code, 'spend_keyword');

        $item['spend_type']  = 2;
        $item['click']       = $item['interactive'];
        $item['interactive'] = (int)Arr::get($item, 'comment_count', 0)
            + (int)Arr::get($item, 'start_count', 0)
            + (int)Arr::get($item, 'share_count', 0);

        return $item;
    }
}
