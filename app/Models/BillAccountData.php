<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Log;

class BillAccountData extends Model
{
    public static $excelFields = [
        "线上客服"    => "online_customer",
        "建档人"     => "archive_by",
        "建档类型"    => "archive_type",
        "网电回访人"   => "online_return_visit_by",
        "媒介类型"    => "medium_type",
        "媒介来源"    => "medium_source",
        "开单"      => "account_by",
        "收费单号"    => "order_form_number",
        "收费单类型"   => "order_type",
        "客户姓名"    => "customer",
        "客户状态"    => "customer_status",
        "二次来院"    => "again_arriving",
        "电话"      => "phone",
        "客户卡号"    => "customer_card_number",
        "结账日期"    => "pay_date",
        "总金额"     => "total",
        "应付金额"    => "payable",
        "实付金额"    => "real_payment",
        "开单业绩"    => "order_account",
        "美容院类型"   => "beauty_salon_type",
        "美容院"     => "beauty_salon_name",
        "财务收支总金额" => "total_pay",
        "返款后业绩"   => "total_account",
        "访客ID"    => "visitor_id",
        "建档时间"    => "archive_date",
    ];

    protected $fillable = [
        'archive_id',
        "online_customer",
        "archive_by",
        "archive_type",
        "online_return_visit_by",
        "medium_type",
        "medium_source",
        "account_by",
        "order_form_number",
        "order_type",
        "customer",
        "customer_status",
        "again_arriving",
        "phone",
        "customer_card_number",
        "pay_date",
        "total",
        "payable",
        "real_payment",
        "order_account",
        "beauty_salon_type",
        "beauty_salon_name",
        "total_pay",
        "total_account",
        "visitor_id",
        "archive_date",
        "type",
        'uuid',
        'customer_id',
        'medium_id',
        'account_id',

        'online_return_visit_by_id',
        'account_by_id',
        'online_customer_id',
        'archive_by_id',
        'client',
        'medium',
    ];

    public static $BillAccountCountDataFormat = [
        'total_account'     => 0,
        'new_account'       => 0,
        'new_again_account' => 0,
        'new_first_account' => 0,
        'old_account'       => 0,
        'new_first_count'   => 0,
        'new_again_count'   => 0,
        'new_count'         => 0,
        'old_count'         => 0,
        // 微博字段
        'comment_account'   => 0,
        'form_account'      => 0,
        'message_account'   => 0,
        'follow_account'    => 0,
        'other_account'     => 0,
    ];

    public function medium()
    {
        return $this->belongsTo(MediumType::class, 'medium_id', 'id');
    }


    public function account()
    {
        return $this->belongsTo(AccountData::class, 'account_id', 'id');
    }

    public function projects()
    {
        return $this->morphToMany(ProjectType::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    public function archiveBy()
    {
        return $this->belongsTo(Consultant::class, 'archive_by_id', 'id');
    }

    public function archive()
    {
        return $this->belongsTo(ArchiveType::class, 'archive_id', 'id');
    }


    public static function fixArchiveBy()
    {
        $data = static::query()
            ->whereNull('online_customer_id')
            ->select([
                'online_return_visit_by',
                'account_by',
                'online_customer',
                'archive_by',
                'type',
                'id',
            ])
            ->get()->each(function ($item) {
                $arr = Helpers::multipleCheckConsultantId($item, $item['type'], [
                    'online_return_visit_by',
                    'account_by',
                    'online_customer',
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

    public static function getBillAccountDataOfDate($client, $start, $end, $count = 1000)
    {
        return $client::accountSearchData([
            'DatetimeCheckoutStart' => $start,
            'DatetimeCheckoutEnd'   => $end,
            'pageSize'              => $count,
            'pageCurrent'           => '1',
            "orderField"            => '',
            "orderDirection"        => '',

        ]);
    }

    public static function generateBillAccountOfData($type, $data, $clientName)
    {
        $uuid = collect();
        collect($data)->filter(function ($data) {
            return isset($data['pay_date']) && isset($data['customer_id']);
        })->each(function ($item) use ($type, $uuid, $clientName) {
            $key = $item['pay_date'] . $item['customer_id'] . $item['order_type'] . $item['order_account'] . $item['project_name'];

            $item['uuid'] = md5($key);

            $item['type']       = $type;
            $item['pay_date']   = Carbon::parse($item['pay_date'])->toDateString();
            $item['medium_id']  = Helpers::getMediumTypeId($item['medium']);
            $item['visitor_id'] = mb_substr(
                $item['visitor_id'] ?? '',
                0,
                Builder::$defaultStringLength
            );
            $item['archive_id'] = Helpers::getArchiveTypeId($item['archive_type']);
            $item['account_id'] = Helpers::crmDataCheckAccount($item, $type);
            $item['client']     = $clientName;

            $consultantResult = Helpers::multipleCheckConsultantId($item, $type, [
                'online_return_visit_by',
                'account_by',
                'online_customer',
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
        $data        = static::getBillAccountDataOfDate($client, $start, $end, $count);
        $uuidList    = static::generateBillAccountOfData($type, $data, $clientName);
        $deleteCount = static::removeNotInDateUUID($uuidList, $clientName, [$start, $end]);

        return $data ? [
            'createCount' => $uuidList->count(),
            'deleteCount' => $deleteCount,
        ] : null;
    }

    public static function removeNotInDateUUID($uuid, $clientName, $dates)
    {
        return static::query()
            ->where('client', $clientName)
            ->whereBetween('pay_date', $dates)
            ->whereNotIn('uuid', $uuid)
            ->delete();
    }


    public static function todayBillAccountData($type, $queue = true)
    {
        $today = Carbon::now()->toDateString();
        if ($queue) {
            return CrmGrabLog::generate($type, 'billAccountData', $today, $today);
        } else {
            return static::getDataOfDate($type, $today, $today);
        }
    }

    public static function yesterdayBillAccountData($type, $queue = true)
    {
        $yesterday = Carbon::yesterday()->toDateString();
        if ($queue) {
            return CrmGrabLog::generate($type, 'billAccountData', $yesterday, $yesterday);
        } else {
            return static::getDataOfDate($type, $yesterday, $yesterday);
        }
    }

    public static function monthBillAccountData($type)
    {
        $date  = Carbon::today();
        $start = $date->firstOfMonth()->toDateString();
        $end   = $date->lastOfMonth()->toDateString();

        $result = collect();
        Helpers::dateRangeForEach([$start, $end], function ($date) use ($type, $result) {
            $dateString = $date->toDateString();
            $data       = CrmGrabLog::generate($type, 'billAccountData', $dateString, $dateString);
            $result->push($data);
        });
        return $result;
    }

    public static function prevMonthBillAccountData($type)
    {
        $date  = Carbon::today();
        $start = $date->firstOfMonth()->toDateString();
        $end   = $date->lastOfMonth()->toDateString();
        return static::getDataOfDate($type, $start, $end);
    }


}
