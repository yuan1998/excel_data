<?php

namespace App\Admin\Actions\SpendData;

use App\Models\FormData;
use App\Models\SpendData;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class RecheckSpendItem extends RowAction
{
    public $name = '重新查询';

    public function handle(SpendData $model)
    {
        SpendData::itemSelfRecheck($model);

        return $this->response()->success('重新匹配已完成,手机号码查询任务已创建.请稍后刷新')->refresh();
    }

}
