<?php

namespace App\Models;

use App\Jobs\CustomerPhoneCheckJob;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array, array $array1)
 */
class CustomerPhone extends Model
{
    protected $fillable = [
        'customer_id',
        'phone',
        'type',
    ];

    public $timestamps = false;

    public static function recheckPhone()
    {
        static::query()->whereNull('phone')
            ->get()->each(function ($phone) {
                CustomerPhoneCheckJob::dispatch($phone)->onQueue('check_customer_phone');
            });
    }
}
