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
        'client',

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
            ->whereNull('online_customer_id')
            ->select([
                'online_customer',
                'return_visit_by',
                'archive_by',
                'type',
                'id',
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

    public static function getTempCustomerDataOfDate($client, $start, $end, $count = 10000)
    {

        return $client::tempSearchData([
            'DatetimeRegStart' => $start,
            'DatetimeRegEnd'   => $end,
            'pageSize'         => $count
        ]);
    }

    public static function tempCustomerDataGenerate($array, $type, $clientName)
    {
        $uuid = collect();
        collect($array)->filter(function ($data) {
            return isset($data['customer_id']);
        })->each(function ($item) use ($type, $uuid, $clientName) {
            $key = $item['customer_id'] . $item['archive_type'] . $item['archive_date'];

            $item['uuid']       = md5($key);
            $item['type']       = $type;
            $item['medium_id']  = Helpers::getMediumTypeId($item['medium']);
            $item['visitor_id'] = mb_substr($item['visitor_id'] ?? '', 0, Builder::$defaultStringLength);
            $item['archive_id'] = Helpers::getArchiveTypeId($item['archive_type']);
            $item['account_id'] = Helpers::crmDataCheckAccount($item, $type);
            $item['client']     = $clientName;

            $consultantResult = Helpers::multipleCheckConsultantId($item, $type, [
                'return_visit_by',
                'online_customer',
                'archive_by',
            ]);
            $item             = array_merge($item, $consultantResult);

            $uuid->push($item['uuid']);

            static::query()
                ->where('uuid', $item['uuid'])
                ->delete();
            static::create(
                $item
            );

            CustomerPhone::firstOrCreate([
                'customer_id'   => $item['customer_id'],
                'type'          => $type,
                'customer_type' => CustomerPhone::$customerType,
            ], [
                'client' => $clientName
            ]);

        });

        return $uuid;
    }

    public static function removeNotInDateUUID($uuid, $clientName, $dates)
    {
        return static::query()
            ->where('client', $clientName)
            ->whereBetween('archive_date', $dates)
            ->whereNotIn('uuid', $uuid)
            ->delete();
    }


    public static function getDataOfDate($clientName, $start, $end, $count = 10000)
    {

        $client = Helpers::typeClient($clientName);
        if (!$client) return null;
        $type = $client::$type;

        $data        = static::getTempCustomerDataOfDate($client, $start, $end, $count);
        $uuid        = static::tempCustomerDataGenerate($data, $type, $clientName);
        $deleteCount = static::removeNotInDateUUID($uuid, $clientName, [$start, $end]);

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

    public static function yesterday($type, $queue = true)
    {

        $date = Carbon::yesterday()->toDateString();

        if ($queue) {
            return CrmGrabLog::generate($type, 'tempCustomerData', $date, $date);
        } else {
            return static::getDataOfDate($type, $date, $date);
        }
    }


}
