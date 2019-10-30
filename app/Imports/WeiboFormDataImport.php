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
use Maatwebsite\Excel\Concerns\ToModel;

class WeiboFormDataImport implements ToCollection
{

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = Helpers::excelToKeyArray($collection, WeiboData::$excelFields);

        $date = Carbon::now()->toDateTimeString();
        collect($data)->filter(function ($item) {
            return isset($item['weibo_id'])
                && isset($item['phone'])
                && !!$item['phone']
                && isset($item['post_date']);
        })->each(function ($item) use ($date) {
            WeiboFormData::updateOrCreate([
                'phone'    => $item['phone'],
                'upload_date' => $date,
            ], $item);
        });
    }
}
