<?php

namespace App\Admin\Actions\Weibo;

use App\Models\WeiboFormData;
use App\Models\WeiboUser;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class BatchDispatch extends BatchAction
{
    public $name = '批量分配';

    public function handle(Collection $model, Request $request)
    {
        $id =  $request->get('id');
        if (!$id) {
            throw new \Exception('错误的ID');
        }

        $model->each(function ($item) use ($id) {
            $item->weibo_user_id = $id;
            $item->save();

        });
        // 获取到表单中的`type`值

        return $this->response()->success('已分配成功')->refresh();
    }

    public function form()
    {
        $options  = WeiboUser::all()->pluck('username' , 'id');

        $this->select('id', '所属人')->options($options)->rules('required');
    }

}
