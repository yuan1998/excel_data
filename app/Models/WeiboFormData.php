<?php

namespace App\Models;

use App\Admin\Actions\WeiboConfigAction;
use App\Clients\WeiboClient;
use App\Helpers;
use App\Jobs\PullWeiboFormData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class WeiboFormData extends Model
{
    protected $fillable = [
        'weibo_id',
        'project_id',
        'project_name',
        'post_date',
        'name',
        'phone',
        'category_type',
        'feedback',
        'comment',
        'weixin',

        'type',
        'tags',
        'remark',
        'real_post_date',
        'weibo_user_id',
        'dispatch_date',
        'upload_date',
        'recall_date',
    ];

    /**
     * 微博标签列表
     * @var array
     */
    public static $TagList = [
        0 => '未标记',
        1 => '转微',
        2 => '未接通',
        3 => '空号',
        4 => '无需求',
        5 => '重复建档',
        6 => '已到院',
        7 => '有需求',
    ];

    /**
     * 关联分配的 客服
     * @return BelongsTo
     */
    public function weiboUser()
    {
        return $this->belongsTo(WeiboUser::class, 'weibo_user_id', 'id');
    }

    /**
     * 将数据转换成可用于创建 FormData 的格式
     * @return array
     */
    public function toFormCreateData()
    {
        return [
            'weibo_id'  => $this->id,
            'data_type' => $this->project_name,
            'form_type' => 2,
            'type'      => $this->type,
            'date'      => $this->post_date,
        ];
    }

    /**
     * 修复方法 : 将 real_post_date 为空的数据,填入real_post_date数据;
     */
    public static function setRealPostDate()
    {
        WeiboFormData::query()
            ->whereNull('real_post_date')
            ->get()
            ->each(function ($weiboFormData) {
                $weiboFormData->real_post_date = $weiboFormData->post_date;
                $weiboFormData->save();
            });
    }

    /**
     * 判断 分配配置 中的暂停时段是否开启
     * @param      $config
     * @param null $testTime
     * @return bool
     */
    public static function stopCheck($config, $testTime = null)
    {
        // 如果 开关 为打开
        if (!$config['all_day']) {
            // 如果 开始时间 和 结束时间 都存在,开始判断
            if ($config['start_time'] && $config['end_time']) {
                // 获取用于对比的时间
                $now   = $testTime ? Carbon::parse($testTime) : Carbon::now();
                $start = Carbon::parse($config['start_time']);
                $end   = Carbon::parse($config['end_time']);

                // 转换时间格式, 例: 09:00:00 => 90000
                $startTime = (int)$start->format('His');
                $endTime   = (int)$end->format('His');
                $nowTime   = (int)$now->format('His');

                // 返回对比结果   start_time <=  now <= $end_time
                return $startTime <= $nowTime && $nowTime <= $endTime;
            }
        }
        return true;
    }

    public function checkRuleUsers()
    {
        $type = $this->type;

        $rules = WeiboDispatchSetting::query()
            ->where('type', $type)
            ->where('dispatch_open', true)
            ->orderBy('order', 'desc')
            ->get();

        if ($rules->isNotEmpty()) {
            $rule = $rules->first(function ($rule) {
                return preg_match(Helpers::explodeKeywordToRegex($rule->keyword), $this->project_name);
            });
            if ($rule) {
                return $rule->toArray();
            }
        }

        return WeiboDispatchSetting::getBaseSettings($type);
    }

    /**
     * 将表单分配给在线客服
     * 判断分配规则  => 是否分配 =>  确认在线客服 => 获取结果
     */
    public function dispatchItem()
    {
        // 获取分配配
        $settings = $this->checkRuleUsers();
        Log::info('微博分配 配置', $settings);

        // 判断分配是否开启
        if ($settings['dispatch_open']) {
            // 判断是否在暂停的时间段内
            $stopDispatch = static::stopCheck($settings);
            Log::info('微博分配 判断时间分配', [$stopDispatch]);

            if ($stopDispatch) {
                // 开始分配 , 获取符合分配条件的客服
                $id = WeiboUser::newDispatchData($settings['dispatch_users']);
                Log::info('微博分配 分配ID', [$id]);

                if ($id) {
                    // 如果ID存在,将表单分配给该客服.
                    WeiboFormData::find($this->id)->update(['weibo_user_id' => $id]);
                    return $id;
                }
            }
        }

    }

    /**
     * 安排未分配的表单重新进行分配
     */
    public static function unallocated()
    {
        static::query()
            ->whereNull('weibo_user_id')
            ->get()
            ->each(function ($item) {
                $item->dispatchItem();
            });
    }

    /**
     * 将拉取回来的后台数据转换成服务器可以存储的字段
     * @param $item
     * @return array
     */
    public static function apiDataParse($item)
    {
        return [
            'weibo_id'       => $item['id'],
            'project_id'     => $item['pageId'],
            'project_name'   => $item['pageName'],
            'post_date'      => Carbon::parse($item['timeAdd'])->toDateString(),
            'real_post_date' => $item['timeAdd'],
            'name'           => $item['userName'],
            'phone'          => $item['userPhone'],
            'comment'        => $item['desc'],
        ];
    }

    /**
     * 创建 微博表单 数据
     * @param $type
     * @param $data
     * @return int
     */
    public static function generateWeiboFormData($type, $data)
    {
        // 获取 当前时间,重复使用.
        $now = Carbon::now()->toDateTimeString();
        collect($data)->each(function ($item) use ($now, $type) {
            // 获取转换基础数据
            $parserItem                = static::apiDataParse($item);
            $parserItem['upload_date'] = $now;
            $parserItem['type']        = $type;

            //使用 phone 和 post_date 判断是否需要创建新的数据
            $model = WeiboFormData::firstOrCreate([
                'phone'     => $parserItem['phone'],
                'post_date' => $parserItem['post_date'],
            ], $parserItem);

            // 如果源数据中 回访记录 为空,但更新数据中存在,则写入 回访记录
            if (!$model->comment && $parserItem['comment']) {
                WeiboFormData::find($model->id)->update([
                    'comment' => $parserItem['comment'],
                ]);
            }
        });
        // 返回拉取的数量
        return count($data);
    }

    /**
     * 使用微博表单 创建 FormData
     * @param null $delay
     */
    public function makeFormData($delay = null)
    {
        // 调用 FormData 的方法创建 FormData 和 FormDataPhone.
        FormData::updateOrCreateItem($this->toFormCreateData(), 'weibo_id', $delay);
    }

    /**
     * 调用客户端,拉取微博后台的表单数据
     * @param     $accountName
     * @param     $startDate
     * @param     $endDate
     * @param int $count
     * @return int
     * @throws \Exception
     */
    public static function pullWeiboData($accountName, $startDate, $endDate, $count = 2000)
    {
        if (!isset(WeiboClient::$Account[$accountName])) {
            throw new \Exception('错误的账户');
        }
        $account = WeiboClient::$Account[$accountName];
        $type    = $account['type'];

        // 传入参数,使用客户端从微博后台拉取数据
        $data = WeiboClient::getWeiboData($account, $startDate, $endDate, $count);

        // 没有数据报错.
        if (!$data) {
            Log::info('拉取微博数据出错 , 数据为空', ['result' => $data, 'account' => $account]);
        }

        // 将拉取到的数据保存到服务器
        return static::generateWeiboFormData($type, $data);
    }

    /**
     * 使用客户端拉取当天的数据
     * @param $type
     */
    public static function pullToday($type)
    {
        // 获取今天的日期
        $today = Carbon::today()->toDateString();
        // 加入查询队列,后台自动查询微博后台数据
        PullWeiboFormData::dispatch($type, $today, $today)->onQueue('pull_weibo_data');
    }

    /**
     * 使用客户端拉取昨天的数据
     * @param $type
     */
    public static function pullYesterday($type)
    {
        // 获取昨天的日期
        $yesterday = Carbon::yesterday()->toDateString();
        // 加入查询队列,后台自动查询微博后台数据
        PullWeiboFormData::dispatch($type, $yesterday, $yesterday)->onQueue('pull_weibo_data');
    }
}
