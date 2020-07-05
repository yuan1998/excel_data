<?php

namespace App\Models;

use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;

class TempCustomerData extends Model
{
    public static $excelFields = [
        "is_order",
        "is_arriving",
        "temp_visitor",
        "phone",
        "gender",
        "archive_type",
        "online_customer",
        "return_visit_count",
        "archive_by",
        "return_visit_by",
        "referrer",
        "archive_date",
        "arriving_date",
        "last_return_visit_date",
        "medium_type",
        "medium_source",
        "beauty_salon_type",
        "beauty_salon_name",
        "tag_name",
        "comment",
        "focus_question",
        "marriage",
        "age",
        "financial_ability",
        "province",
        "county",
        "area",
        "address",
        "career",
        "weixin",
        "qq",
        "reservation_number",
        "visitor_id",
        "staff_referrer",
    ];

    protected $fillable = [
        "is_order",
        "is_arriving",
        "temp_visitor",
        "phone",
        "gender",
        "archive_type",
        "online_customer",
        "return_visit_count",
        "archive_by",
        "return_visit_by",
        "referrer",
        "archive_date",
        "arriving_date",
        "last_return_visit_date",
        "medium_type",
        "medium_source",
        "beauty_salon_type",
        "beauty_salon_name",
        "tag_name",
        "comment",
        "focus_question",
        "marriage",
        "age",
        "financial_ability",
        "province",
        "county",
        "area",
        "address",
        "career",
        "weixin",
        "qq",
        "reservation_number",
        "visitor_id",
        "staff_referrer",

        'uuid',
        'archive_id',
        'customer_id',
        'medium_id',
        'account_id',
        'type',

        'online_customer_id',
        'return_visit_by_id',
        'archive_by_id',
    ];

    public function archiveBy()
    {
        return $this->belongsTo(Consultant::class, 'archive_by_id', 'id');
    }

    public static function fixArchiveBy()
    {
        $data = static::query()
            ->doesntHave('archiveBy')
            ->select([
                'online_customer',
                'return_visit_by',
                'archive_by',
                'type'
            ])
            ->get()->each(function ($item) {
                $arr = Helpers::multipleCheckConsultantId($item, $item['type'], [
                    'online_customer',
                    'return_visit_by',
                    'archive_by',
                ]);
                $item->update($arr);
            });
        return $data->count();
    }

    public static function getTempCustomerDataOfCrm($data, $type)
    {
        $client = Helpers::typeClient($type);
        if (!$client) {
            return [];
        }
        return $client::tempSearchData($data);
    }

    public static function getTempCustomerDataOfDate($type, $start, $end, $count = 10000)
    {
        return static::getTempCustomerDataOfCrm([
            'DatetimeRegStart' => $start,
            'DatetimeRegEnd'   => $end,
            'pageSize'         => $count
        ], $type);
    }

    public static function tempCustomerDataGenerate($array, $type)
    {
        $uuid = collect();
        collect($array)->filter(function ($data) {
            return isset($data['customer_id']);
        })->each(function ($item) use ($type, $uuid) {
            $key = $item['customer_id'] . $item['archive_type'] . $item['archive_date'];

            $item['uuid']       = md5($key);
            $item['type']       = $type;
            $item['medium_id']  = Helpers::getMediumTypeId($item['medium']);
            $item['visitor_id'] = mb_substr($item['visitor_id'] ?? '', 0, Builder::$defaultStringLength);
            $item['archive_id'] = Helpers::getArchiveTypeId($item['archive_type']);
            $item['account_id'] = Helpers::crmDataCheckAccount($item, $type);

            $consultantResult = Helpers::multipleCheckConsultantId($item, $type, [
                'return_visit_by',
                'online_customer',
                'archive_by',
            ]);
            $item             = array_merge($item, $consultantResult);

            $uuid->push($item['uuid']);

            static::updateOrCreate([
                'uuid' => $item['uuid'],
            ], $item);
            CustomerPhone::firstOrCreate([
                'customer_id'   => $item['customer_id'],
                'type'          => $type,
                'customer_type' => 'temp_cust_info_cross',
            ]);
        });

        return $uuid;
    }

    public static function removeNotInDateUUID($uuid, $type, $dates)
    {
        return static::query()
            ->where('type', $type)
            ->whereBetween('archive_date', $dates)
            ->whereNotIn('uuid', $uuid)
            ->delete();
    }


    public static function getDataOfDate($type, $start, $end, $count = 10000)
    {
        $data        = static::getTempCustomerDataOfDate($type, $start, $end, $count);
        $uuid        = static::tempCustomerDataGenerate($data, $type);
        $deleteCount = static::removeNotInDateUUID($uuid, $type, [$start, $end]);

        return $data ? [
            'createCount' => $uuid->count(),
            'deleteCount' => $deleteCount,
        ] : null;
    }

    public static function getToday($type, $queue = true)
    {
        $date = Carbon::today()->toDateString();
        if ($queue) {
            return CrmGrabLog::generate($type, 'tempCustomerData', $date, $date);
        } else {
            return static::getDataOfDate($type, $date, $date);
        }
    }

    public static function yesterday($type)
    {

        $date = Carbon::yesterday()->toDateString();

        return CrmGrabLog::generate($type, 'tempCustomerData', $date, $date);
    }


}
