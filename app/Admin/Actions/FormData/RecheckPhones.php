<?php

namespace App\Admin\Actions\FormData;

use App\Models\FormData;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class RecheckPhones extends RowAction
{
    public $name = '重新查询(手机)';

    public function handle(FormData $model)
    {
        $model->itemRecheckPhone();

        return $this->response()->success('已创建单独查询任务,请稍后刷新');
    }

}
