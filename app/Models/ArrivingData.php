<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArrivingData extends Model
{

    public static $excelFields = [
        "客户ID"   => 'customer_id',
        "是否成交"   => "is_transaction",
        "客户状态"   => "customer_status",
        "二次来院"   => "again_arriving",
        "客户"     => "customer",
        "电话"     => "phone",
        "性别"     => "gender",
        "年龄"     => "age",
        "访客ID"   => "visitor_id",
        "项目需求"   => "project_demand",
        "线上客服"   => "online_customer",
        "网电建档人"  => "online_archive_by",
        "媒介"     => "medium",
        "建档类型"   => "archive_type",
        "美容院类型"  => "beauty_salon_type",
        "美容院名称"  => "beauty_salon_name",
        "网电回访人"  => "online_return_visit_by",
        "医生"     => "doctor",
        "临客建档时间" => "temp_archive_date",
        "建档人"    => "archive_by",
        "接待时间"   => "reception_date",
        "实付款"    => "real_payment",
        "应付款"    => "payable",
        "收费单类型"  => "order_type",
        "结账时间"   => "pay_date",
        "接诊单编号"  => "reception_form_number",
        "收费单编号"  => "order_form_number",
        "预约单编号"  => "reservation_form_number",
        "咨询意向度"  => "intention",
        "科室"     => "department",
        "预约专家"   => "reservation_expert",
        "推荐人"    => "referrer_by",
        "推荐关系"   => "referrer_relation",
        "客户卡号"   => "customer_card_number",
        "QQ"     => "qq",
        "微信"     => "weixin",
        "省份"     => "province",
        "城市"     => "city",
        "员工推荐人"  => "staff_referrer",
        "备注"     => "comment",
    ];

    protected $fillable = [
        'uuid',
        'archive_id',
        'type',
        'customer_id',
        "is_transaction",
        "customer_status",
        "again_arriving",
        "customer",
        "phone",
        "gender",
        "age",
        "visitor_id",
        "project_demand",
        "online_customer",
        "online_archive_by",
        "medium",
        "archive_type",
        "beauty_salon_type",
        "beauty_salon_name",
        "online_return_visit_by",
        "doctor",
        "temp_archive_date",
        "archive_by",
        "reception_date",
        "real_payment",
        "payable",
        "order_type",
        "pay_date",
        "reception_form_number",
        "order_form_number",
        "reservation_form_number",
        "intention",
        "department",
        "reservation_expert",
        "referrer_by",
        "referrer_relation",
        "customer_card_number",
        "qq",
        "weixin",
        "province",
        "city",
        "staff_referrer",
        "comment",
        "medium_id",
        "account_id",

        'online_return_visit_by_id',
        'online_customer_id',
        'online_archive_by_id',
        'archive_by_id',
        'client',
    ];

    public static $ArrivingCountDataFormat = [
        'count'                  => 0,
        'new_first'              => 0,
        'new_again'              => 0,
        'new_total'              => 0,
        'old'                    => 0,
        'arriving_count'         => 0,
        'new_transaction'        => 0,
        'new_first_transaction'  => 0,
        'new_again_transaction'  => 0,
        'old_transaction'        => 0,
        // 微博字段
        'first_arriving_comment' => 0,
        'first_arriving_form'    => 0,
        'first_arriving_message' => 0,
        'first_arriving_follow'  => 0,
        'first_arriving_other'   => 0,
        'again_arriving_comment' => 0,
        'again_arriving_form'    => 0,
        'again_arriving_message' => 0,
        'again_arriving_follow'  => 0,
        'again_arriving_other'   => 0,
        'old_arriving_comment'   => 0,
        'old_arriving_form'      => 0,
        'old_arriving_message'   => 0,
        'old_arriving_follow'    => 0,
        'old_arriving_other'     => 0,


    ];

    public function account()
    {
        return $this->belongsTo(AccountData::class, 'account_id', 'id');
    }

    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    public function archive()
    {
        return $this->belongsTo(ArchiveType::class, 'archive_id', 'id');
    }

    public function medium()
    {
        return $this->belongsTo(MediumType::class, 'medium_id', 'id');
    }

    public function onlineArchiveBy()
    {
        return $this->belongsTo(Consultant::class, 'online_archive_by_id', 'id');
    }

    public function onlineCustomer()
    {
        return $this->belongsTo(Consultant::class, 'online_customer_id', 'id');
    }

    public static function fixOnlineArchiveBy()
    {
        $data = static::query()
            ->whereNull('online_customer_id')
            ->select([
                'online_return_visit_by',
                'online_customer',
                'online_archive_by',
                'archive_by',
                'type',
                'id',
            ])
            ->get()
            ->each(function ($item) {
                $arr = Helpers::multipleCheckConsultantId($item, $item['type'], [
                    'online_return_visit_by',
                    'online_customer',
                    'online_archive_by',
                    'archive_by',
                ]);
                $item->update($arr);
            });
        return $data->count();
    }


    public static function recheckProjects()
    {
        static::all()->each(function ($item) {
            $item->update([
                'archive_id' => Helpers::getArchiveTypeId($item->archive_type),
            ]);
        });
    }


    public static function getArrivingDataOfDate($client, $start, $end, $count = 10000)
    {
        return $client::toHospitalSearchData([
            'DatetimeRegStart' => $start,
            'DatetimeRegEnd'   => $end,
            'pageSize'         => $count
        ]);
    }

    public static function arrivingDataGenerate($array, $type, $clientName)
    {

        $uuid = collect();
        collect($array)
            ->filter(function ($data) {

                return isset($data['reception_date']) && isset($data['customer_id']);
            })
            ->each(function ($item) use ($type, $uuid, $clientName) {
                $key = $item['reception_date'] . $item['customer_id'] . $item['order_type'] . $item['payable'] . $item['real_payment'];

                $item['uuid']       = md5($key);
                $item['type']       = $type;
                $item['medium_id']  = Helpers::getMediumTypeId($item['medium']);
                $item['visitor_id'] = mb_substr($item['visitor_id'] ?? '', 0, Builder::$defaultStringLength);
                $item['archive_id'] = Helpers::getArchiveTypeId($item['archive_type']);
                $item['account_id'] = Helpers::crmDataCheckAccount($item, $type);
                $item['client']     = $clientName;

                $consultantResult = Helpers::multipleCheckConsultantId($item, $type, [
                    'online_return_visit_by',
                    'online_customer',
                    'online_archive_by',
                    'archive_by',
                ]);
                $item             = array_merge($item, $consultantResult);

                $uuid->push($item['uuid']);
                static::updateOrCreate([
                    'uuid' => $item['uuid'],
                ], $item);

            });
        return $uuid;
    }

    public static function getDataOfDate($clientName, $start, $end, $count = 10000)
    {
        $client = Helpers::typeClient($clientName);
        if (!$client) return null;
        $type        = $client::$type;
        $data        = static::getArrivingDataOfDate($client, $start, $end, $count);
        $uuid        = static::arrivingDataGenerate($data, $type, $clientName);
        $deleteCount = static::removeNotInDateUUID($uuid, $clientName, [$start, $end]);

        return $data ? [
            'createCount' => $uuid->count(),
            'deleteCount' => $deleteCount,
        ] : null;
    }

    public static function removeNotInDateUUID($uuid, $clientName, $dates)
    {
        return static::query()
            ->where('client', $clientName)
            ->whereBetween('reception_date', $dates)
            ->whereNotIn('uuid', $uuid)
            ->delete();
    }


    public static function getYesterday($type, $queue = true)
    {
        $date = Carbon::yesterday()->toDateString();
        if ($queue) {
            return CrmGrabLog::generate($type, 'arrivingData', $date, $date);
        } else {
            return static::getDataOfDate($type, $date, $date);
        }
    }

    public static function getToday($type, $queue = true)
    {
        $date = Carbon::today()->toDateString();
        if ($queue) {
            return CrmGrabLog::generate($type, 'arrivingData', $date, $date);
        } else {
            return static::getDataOfDate($type, $date, $date);
        }
    }

    public static function getCurrentMonth($type)
    {
        $date   = Carbon::today();
        $start  = $date->firstOfMonth()->toDateString();
        $end    = $date->lastOfMonth()->toDateString();
        $result = collect();
        Helpers::dateRangeForEach([$start, $end], function ($date) use ($type, $result) {
            $dateString = $date->toDateString();
            $data       = CrmGrabLog::generate($type, 'arrivingData', $dateString, $dateString);
            $result->push($data);
        });
        return $result;
    }

    public function scopeDateBetween($query, $start, $end)
    {
        return $query->whereBetween('reception_date', [$start, $end]);
    }

}
