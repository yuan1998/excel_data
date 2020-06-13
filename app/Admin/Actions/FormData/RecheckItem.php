<?php

namespace App\Admin\Actions\FormData;

use App\Models\FormData;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class RecheckItem extends RowAction
{
    public $name = '重新查询';

    public function handle(FormData $model)
    {
        $model->itemRecheck();

        return $this->response()->success('重新匹配已完成,手机号码查询任务已创建.请稍后刷新')->refresh();
    }

}
