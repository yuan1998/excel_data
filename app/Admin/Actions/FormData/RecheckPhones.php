<?php

namespace App\Admin\Actions\FormData;

use App\Models\FormData;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class RecheckPhones extends RowAction
{
    public $name = '重新查询(手机)';

    public function actionScript()
    {
        return <<<SCIPRT
//<![CDATA[
         Swal.fire({
                title            : '',
                html             : '<div class="save_loading"><svg viewBox="0 0 140 140" width="140" height="140"><g class="outline"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="rgba(0,0,0,0.1)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round">\<\/path>\<\/g><g class="circle"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="#71BBFF" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-dashoffset="200" stroke-dasharray="300">\<\/path>\<\/g>\<\/svg>\<\/div><div><h4>重新查询中,请稍等...\<\/h4>\<\/div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
   
//]]>

SCIPRT;
    }

    public function handle(FormData $model)
    {
        foreach ($model->phones as $phone) {
            $phone->checkCrmInfo();
        }

        return $this->response()->swal()->success('查询成功,稍后刷新页面查看结果')->refresh();
    }

}
