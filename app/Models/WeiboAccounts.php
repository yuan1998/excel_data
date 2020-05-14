<?php

namespace App\Models;

use App\Helpers;
use App\Jobs\PullWeiboFormData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class WeiboAccounts extends Model
{
    protected $fillable = [
        'name',
        'username',
        'password',
        'begin_time',
        'end_time',
        'customer_id',
        'active',
        'all_day',
    ];

    public static $_GRAB_SETTINGS_NAME_ = "weibo_data_grab_settings_name_prefix_test_";

    public static function setAccountData()
    {
        $data = static::all();
        Redis::set(static::$_GRAB_SETTINGS_NAME_, $data->toJson());
    }

    public static function getAccountData()
    {
        $data = Redis::get(static::$_GRAB_SETTINGS_NAME_);

        return $data ? json_decode($data, true) : static::setAccountData();
    }

    public static function checkAccountIsRun($beginDay, $endDay)
    {
        $account = static::getAccountData();
        $index   = 0;

        Log::info('抓取 Debug');
        Log::info('抓取账号', [$account]);
        foreach ($account as $item) {
            if ($item['active'] && ($item['all_day'] || Helpers::timeBetween($item['begin_time'], $item['end_time']))) {
                Log::info('抓取启动', [$item['name']]);

                PullWeiboFormData::dispatch($item['id'], $beginDay, $endDay)
                    ->onQueue('pull_weibo_data')
                    ->delay(now()->addMinutes($index * 5));
                $index++;
            }
        }

    }


}
