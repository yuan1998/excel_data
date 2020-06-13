<?php

namespace App\Admin\Actions\FormData;

use App\Models\FormData;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BatchRecheckPhones extends BatchAction
{
    public $name = '重新查询(手机)';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            $model->itemRecheckPhone();
        }

        return $this->response()->success('已创建单独查询任务,请稍后刷新');
    }

}
