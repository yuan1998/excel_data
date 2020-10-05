<?php

namespace App\Models;

use App\Helpers;
use App\Jobs\CustomerPhoneCheckJob;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array, array $array1)
 * @method static CustomerPhone firstOrCreate(array $array, array $array1 = [])
 */
class CustomerPhone extends Model
{
    protected $fillable = [
        'customer_type',
        'customer_id',
        'phone',
        'type',
        'client',
    ];

    public static $tempCustomerType = 'temp_cust_info_cross';
    public static $customerType = 'cust_info';

    public $timestamps = false;

    public static $checkPhoneUrl = [
        "cust_info"            => '/CommonArea/CustInfo/Custinfos',
        "temp_cust_info_cross" => '/CommonArea/TempCustInfo/TempCustinfos',
    ];


    public static function recheckPhone($queue = false, $limit = null)
    {
        $phones = static::query()->whereNull('phone');

        if ($limit)
            $phones->limit($limit);

        $phones = $phones->get();

        foreach ($phones as $phone) {
            if ($queue) {
                CustomerPhoneCheckJob::dispatch($phone->id)->onQueue('check_customer_phone');
            } else {
                $phone->checkPhone();
            }
        }
    }

    public function checkPhone()
    {
        $clientName = $this->client ?? $this->type;
        if (!$client = Helpers::typeClient($clientName)) return;

        $url = data_get(static::$checkPhoneUrl, $this->customer_type);
        if (!$url) return;

        $data = [
            'id' => $this->customer_id,
        ];

        $response = $client::postUriGetDom($url, $data, false);
        preg_match_all('/1[3456789]\d{9}(?!\w)/', $response, $matches);

        $phone = collect($matches[0])->unique()->join(',');
        if ($phone && $phone != $this->phone) {
            $this->update([
                'phone' => $phone,
            ]);
        }
        return $phone;
    }



}
