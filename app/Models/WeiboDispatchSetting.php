<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class WeiboDispatchSetting extends Model
{

    protected $fillable = [
        'type',
        'dispatch_open',
        'dispatch_users',
        'all_day',
        'start_time',
        'end_time',
        'keyword',
        'rule_name',
    ];

    protected $casts = [
        'dispatch_users' => 'json',
        'all_day'        => 'boolean',
        'dispatch_open'  => 'boolean',
    ];

    public static $_PREFIX_BASE_SETTINGS_NAME_ = 'weibo_user_settings_name_prefix_test_';

    public static $defaultDispatchSetting = [
        // 分配开关
        'dispatch_open'  => true,
        // 分配用户
        'dispatch_users' => [],
        // 分配时间
        'all_day'        => false,
        'start_time'     => '9:00:00',
        'end_time'       => '22:00:00',
    ];

    /**
     * 获取基础 分配配置
     * @param string $type
     * @return array|mixed
     */
    public static function getBaseSettings($type)
    {
        $settings = Redis::get(static::$_PREFIX_BASE_SETTINGS_NAME_ . $type);
        return $settings ? json_decode($settings, true) : static::$defaultDispatchSetting;
    }

    /**
     * 写入基础 分配配置
     * @param $type
     * @param $data
     * @return bool
     */
    public static function setBaseSettings($type, $data)
    {
        if (!isset($data['dispatch_users']) || !isset($data['dispatch_open'])) {
            return false;
        }
        return Redis::set(static::$_PREFIX_BASE_SETTINGS_NAME_ . $type, json_encode($data));
    }

}
