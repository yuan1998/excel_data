<?php

namespace App\Observers;

use App\Models\RecallLog;
use App\Models\WeiboFormData;
use App\Models\WeiboUser;
use Carbon\Carbon;
use Dingo\Api\Auth\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class WeiboFormDataObserver
{
    /**
     * 在 微博数据 创建完成之后的事件
     * Handle the weibo form data "created" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function created(WeiboFormData $weiboFormData)
    {
        Log::info('微博分配开始');
        $weiboFormData->dispatchItem();
        Log::info('微博分配结束');
    }

    /**
     * 在 微博数据 修改完成之后的事件
     * Handle the weibo form data "updated" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function updated(WeiboFormData $weiboFormData)
    {
        // 获取被修改的字段
        $changes = $weiboFormData->getChanges();
        Log::info('weibo form data change', $changes);

        // 判断是否是分配操作
        if (isset($changes['weibo_user_id']) && $changes['weibo_user_id']) {

            // 写入分配时间
            WeiboFormData::find($weiboFormData->id)
                ->update([
                    'dispatch_date' => Carbon::now()->toDateTimeString()
                ]);
        }

        // 判断是否希尔 回访时间
        $comment = Arr::get($changes, 'comment', null);
        $tags    = Arr::get($changes, 'tags', null);
        if ($comment || $tags) {
            if (!$weiboFormData->recall_date) {
                WeiboFormData::find($weiboFormData->id)
                    ->update([
                        'recall_date' => Carbon::now()->toDateTimeString()
                    ]);
            }

            $user = auth()->guard('weibo')->user();
            RecallLog::create([
                'comment'       => $comment ?? $weiboFormData->comment,
                'tags'          => $tags ?? $weiboFormData->tags,
                'weibo_user_id' => $user ? $user->id : null,
                'weibo_form_id' => $weiboFormData->id,
            ]);
        }


        // 如果 微博表单 已经成功回访,调用方法创建 FormData, 在30分钟后查询是否已建档
        if (isset($changes['recall_date']) && $changes['recall_date']) {
            $weiboFormData->makeFormData(Carbon::now()->addMinutes(30));
        }
    }

    /**
     * Handle the weibo form data "deleted" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function deleted(WeiboFormData $weiboFormData)
    {
        //
    }

    /**
     * Handle the weibo form data "restored" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function restored(WeiboFormData $weiboFormData)
    {
        //
    }

    /**
     * Handle the weibo form data "force deleted" event.
     *
     * @param WeiboFormData $weiboFormData
     * @return void
     */
    public function forceDeleted(WeiboFormData $weiboFormData)
    {
        //
    }
}
