<?php

namespace App\Models;

use App\Admin\Actions\WeiboConfigAction;
use App\Clients\WeiboClient;
use App\Helpers;
use App\Jobs\PullWeiboFormData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * @method static firstOrCreate(array $array, $item)
 */
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

    public function recallLog()
    {
        return $this->hasMany(RecallLog::class, 'weibo_form_id', 'id');
    }

    /**
     * 关联分配的 客服
     * @return BelongsTo
     */
    public function weiboUser()
    {
        return $this->belongsTo(WeiboUser::class, 'weibo_user_id', 'id');
    }


    /**
     * @param Builder   $query
     * @param WeiboUser $user
     * @return mixed
     */
    public function scopeUserIndex($query, $user)
    {
        $data = request()->only(['tags', 'phone', 'is_recall', 'dispatch_dates']);

        if (isset($data['tags'])) {
            $tags = $data['tags'];
            if ($tags == 0) {
                $query->whereNull('tags');
            } else {
                $query->where('tags', $tags);
            }
        }
        if (isset($data['phone'])) {
            $query->where('phone', $data['phone']);
        }
        if (isset($data['is_recall'])) {
            if ($data['is_recall']) {
                $query->whereNotNull('recall_date');
            } else {
                $query->whereNull('recall_date');
            }
        }
        if (isset($data['dispatch_dates'])) {
            $dates = $data['dispatch_dates'];
            $query->whereBetween('dispatch_date', $dates);
        }
        return $query->where('weibo_user_id', $user->id);
    }

    /**
     * 将数据转换成可用于创建 FormData 的格式
     * @return array
     */
    public function toFormCreateData()
    {
        return [
            'model_id'        => $this->id,
            'model_type'      => static::class,
            'data_type'       => $this->project_name,
            'account_keyword' => $this->project_name,
            'form_type'       => 2,
            'type'            => $this->type,
            'phone'           => $this->phone,
            'date'            => $this->post_date,
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

    public static function fixFormDataOfWeibo()
    {
        FormData::query()
            ->whereNotNull('weibo_id')
            ->delete();

        static::all()
            ->each(function ($weibo) {
                $weibo->makeFormData();
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
            'model_id'       => $item['id'],
            'model_type'     => static::class,
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
        foreach ($data as $item) {
            // 获取转换基础数据
            $parserItem                = static::apiDataParse($item);
            $parserItem['upload_date'] = $now;

            //使用 phone 和 post_date 判断是否需要创建新的数据
            $model = WeiboFormData::firstOrCreate([
                'phone'     => $parserItem['phone'],
                'post_date' => $parserItem['post_date'],
                'type'      => $type,
            ], $parserItem);

            // 如果源数据中 回访记录 为空,但更新数据中存在,则写入 回访记录
            if (!$model->comment && $parserItem['comment']) {
                WeiboFormData::find($model->id)->update([
                    'comment' => $parserItem['comment'],
                ]);
            }
        }
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
        FormData::updateOrCreateItem($this->toFormCreateData(), static::class, $delay);
    }

    /**
     * 调用客户端,拉取微博后台的表单数据
     * @param     $accountName
     * @param     $startDate
     * @param     $endDate
     * @param int $count
     * @return int |void
     * @throws \Exception
     */
    public static function pullWeiboData($accountName, $startDate, $endDate, $count = 2000)
    {
        Log::info('开始拉取微博数据', [$accountName]);
        if (!isset(WeiboClient::$Account[$accountName])) {
            Log::error('错误的账户.', [$accountName]);
            return null;
        }
        $account = WeiboClient::$Account[$accountName];
        $type    = $account['type'];

        // 传入参数,使用客户端从微博后台拉取数据
        $data = WeiboClient::getWeiboData($account, $startDate, $endDate, $count);

        // 没有数据报错.
        if (!$data) {
            Log::info('拉取微博数据出错 , 数据为空', ['result' => $data, 'account' => $accountName]);
        } else {
            $count = count($data);
            Log::info('拉取微博数据结果', [
                'count'   => $count,
                'Account' => $accountName
            ]);

            // 将拉取到的数据保存到服务器
            static::generateWeiboFormData($type, $data);
            return $count;
        }

    }

    public static function pullTodayAllType()
    {
        foreach (WeiboClient::$Account as $accountName => $value) {
            WeiboFormData::pullToday($accountName);
        }
    }


    public static function pullAllTypeOfDate($startDate, $endDate)
    {
        foreach (WeiboClient::$Account as $accountName => $value) {
            PullWeiboFormData::dispatch($accountName, $startDate, $endDate)->onQueue('pull_weibo_data');
        }
    }


    /**
     * 使用客户端拉取当天的数据
     * @param $type
     */
    public static function pullToday($type)
    {
        Log::info('拉取当天的微博表单', [$type]);
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

    public static function testDataMake()
    {
        static::all()
            ->each(function ($data) {
                $data->makeFormData();
            });
    }

    /**
     * @param Collection $data
     * @return bool
     */
    public static function isModel($data)
    {
        $first = $data->get(0);
        return $first
            && $first->contains('序号')
            && $first->contains('项目ID')
            && $first->contains('项目名称')
            && $first->contains('提交时间')
            && $first->contains('电话')
            && $first->contains('分组类型');
    }


    /**
     * @param $data
     * @return int
     * @throws \Exception
     */
    public static function excelCollection($data)
    {
        $data = Helpers::excelToKeyArray($data, WeiboData::$excelFields);
        $data = collect($data)->filter(function ($item) {
            return isset($item['weibo_id'])
                && isset($item['phone'])
                && !!$item['phone']
                && isset($item['post_date']);
        });

        return static::handleExcelData($data);
    }

    /**
     * @param $data
     * @return int
     * @throws \Exception
     */
    public static function handleExcelData($data)
    {
        $count = 0;
        foreach ($data as $item) {
            $item = static::parserData($item);

            $model = WeiboFormData::firstOrCreate([
                'phone'     => $item['phone'],
                'post_date' => $item['post_date'],
                'type'      => $item['type'],
            ], $item);

            if (!$model->comment && $item['comment']) {
                WeiboFormData::find($model->id)
                    ->update([
                        'comment' => $item['comment'],
                    ]);
            }
            $count++;
        }
        return $count;
    }

    /**
     * @param $item
     * @return string
     */
    public static function parserDataCode($item)
    {
        $result = $item['project_name'];
        $keys   = array_values(WeiboData::$excelFields);
        foreach ($item as $key => $value) {
            if (!in_array($key, $keys)) {
                $result .= '-' . ($value || '');
            }
        }
        return $result;
    }

    /**
     * @param      $item
     * @param null $type
     * @return mixed
     * @throws \Exception
     */
    public static function parserData($item, $type = null)
    {
        $item['real_post_date'] = $item['post_date'];
        $item['post_date']      = Carbon::parse($item['post_date'])->toDateString();
        $item['upload_date']    = Carbon::now()->toDateTimeString();
        $item['code']           = static::parserDataCode($item);

        if ($type) {
            $item['type'] = $type;
        } elseif (!$departmentType = Helpers::checkDepartment($item['code'])) {
            throw new \Exception('无法判断科室:' . $item['code']);
        } else {
            $item['type'] = $departmentType->type;
        }
        return $item;
    }

}
