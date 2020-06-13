<?php

namespace App\Admin\Actions\FormData;

use App\Models\FormData;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BatchRecheckItem extends BatchAction
{
    public $name = '重新查询';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            $model->itemRecheck();
        }

        return $this->response()->success('重新匹配已完成,手机号码查询任务已创建.请稍后刷新')->refresh();
    }

}
