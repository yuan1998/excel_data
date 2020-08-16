<?php

namespace App\Models;

use App\Clients\WeiboClient;
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
        'channel_id',
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
        $index   = 0;
        $account = WeiboAccounts::query()
            ->where('active', 1)
            ->where('login_status', 1)
            ->get();

        if (!$account) {
            Log::info('微博数据拉取 : 没有符合条件的账户,停止拉取');
            return;
        }

        Log::info('微博数据拉取 : 开启拉取账户数据', [$account->pluck('username')]);

        foreach ($account as $item) {
            $isRunTime = (!!$item['all_day'] || Helpers::timeBetween($item['begin_time'], $item['end_time']));

            if ($isRunTime) {
                Log::info('抓取启动', [$item['name']]);

                PullWeiboFormData::dispatch($item['id'], $beginDay, $endDay)
                    ->onQueue('pull_weibo_data')
                    ->delay(now()->addMinutes($index * 5));
                $index++;
            }

        }
    }

    public function pullAccountFormData($start, $end, $count = 1000, $page = 1)
    {
        $id          = $this->id;
        $weiboClient = new WeiboClient($id);
        if (!$weiboClient->isLogin()) {
            Log::info("拉取微博账户表单 : 账户登录状态错误", [$this->username]);
            $this->login_status = 0;
            $this->save();
            return false;
        }
        $data = $weiboClient->mapFormListToGet($this->customer_id, $start, $end, $count, $page);

        if (!$data)
            Log::info("拉取微博账户表单 : 获取表单数据失败,没有返回结果", [$this->username]);
        elseif ($data['code'] === 10000) {
            Log::info("拉取微博账户表单 : 获取表单数据成功", [$this->username]);
            $dataResult = $data['result'];
            $list       = $dataResult['data'];
            WeiboFormData::generateWeiboFormData($this->type, $list, $this);

            $total = (int)$dataResult['total'];
            if (($count * $page) < $total) {
                $this->pullAccountFormData($start, $end, $count, $page + 1);
            }
            return $total;
        } else {
            Log::info("拉取微博账户表单 : 获取表单数据成功,但结果在意料之外", [
                'username' => $this->username,
                'data'     => $data
            ]);
        }

        return false;
    }


}
