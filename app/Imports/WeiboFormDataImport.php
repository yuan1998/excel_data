<?php

namespace App\Imports;

use App\Helpers;
use App\Models\FormData;
use App\Models\FormDataPhone;
use App\Models\WeiboData;
use App\Models\WeiboFormData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class WeiboFormDataImport implements ToCollection
{

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // 将表单数据 转换成可用以存储的 数组
        $data = Helpers::excelToKeyArray($collection, WeiboData::$excelFields);

        // 当前时间
        $date = Carbon::now()->toDateTimeString();

        // 筛选不合格的数据
        collect($data)->filter(function ($item) {
            return isset($item['weibo_id'])
                && isset($item['phone'])
                && !!$item['phone']
                && isset($item['post_date']);
        })->each(function ($item) use ($date) {
            // 转换基础字段
            $item['real_post_date'] = $item['post_date'];
            $item['post_date']      = Carbon::parse($item['post_date'])->toDateString();
            $item['upload_date']    = $date;

            //使用 phone 和 post_date 判断是否需要创建新的数据
            $model = WeiboFormData::firstOrCreate([
                'phone'     => $item['phone'],
                'post_date' => $item['post_date'],
            ], $item);

            // 如果源数据中 回访记录 为空,但更新数据中存在,则写入 回访记录
            if (!$model->comment && $item['comment']) {
                WeiboFormData::find($model->id)->update([
                    'comment' => $item['comment'],
                ]);
            }
        });
    }
}
