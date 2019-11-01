<?php

namespace App\Models;

use App\Helpers;
use App\Jobs\PullWeiboFormData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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

        'tags',
        'remark',
        'weibo_user_id',
        'dispatch_date',
        'upload_date',
        'recall_date',
    ];

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

    public function weiboUser()
    {
        return $this->belongsTo(WeiboUser::class, 'weibo_user_id', 'id');
    }

    public static function myCreate($condition, $attributes)
    {
        $query = static::query();
        foreach ($condition as $key => $value) {
            $query->where($key, $value);
        }
    }

    public function dispatchItem()
    {
        if ($id = WeiboUser::newDispatchData()) {
            Log::info('dispatch user id.', ['id' => $id]);
            WeiboFormData::find($this->id)->update(['weibo_user_id' => $id]);
        }
    }

    public static function unallocated()
    {
        if (WeiboUser::newDispatchData()) {
            static::query()
                ->whereNull('weibo_user_id')
                ->get()
                ->each(function ($item) {
                    $item->dispatchItem();
                });
        }
    }

    public static function apiDataParse($item)
    {
        return [
            'weibo_id'     => $item['id'],
            'project_id'   => $item['pageId'],
            'project_name' => $item['pageName'],
            'post_date'    => Carbon::parse($item['timeAdd'])->toDateString(),
            'name'         => $item['userName'],
            'phone'        => $item['userPhone'],
            'comment'      => $item['desc'],
        ];
    }

    public static function generateWeiboFormData($data)
    {
        $now = Carbon::now()->toDateTimeString();
        collect($data)->each(function ($item) use ($now) {
            $parserItem                = static::apiDataParse($item);
            $parserItem['upload_date'] = $now;

            $model = WeiboFormData::firstOrCreate([
                'phone'     => $parserItem['phone'],
                'post_date' => $parserItem['post_date'],
            ], $parserItem);
            if (!$model->comment && $parserItem['comment']) {
                $model->comment     = $parserItem['comment'];
                $model->recall_date = $now;
                $model->save();
            }
        });
        return count($data);
    }

    public static function pullWeiboData($startDate, $endDate, $count = 2000)
    {
        $data = Helpers::getWeiboData($startDate, $endDate, $count);
        if (!$data) {
            throw new \Exception('拉取微博数据出错.');
        }
        return static::generateWeiboFormData($data);
    }

    public static function pullToday()
    {
        $today = Carbon::today()->toDateString();
        PullWeiboFormData::dispatch($today, $today)->onQueue('pull_weibo_data');
    }

    public static function pullYesterday()
    {
        $yesterday = Carbon::yesterday()->toDateString();
        PullWeiboFormData::dispatch($yesterday, $yesterday)->onQueue('pull_weibo_data');
    }
}
