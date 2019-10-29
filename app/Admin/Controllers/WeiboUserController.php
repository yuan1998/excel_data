<?php

namespace App\Admin\Controllers;

use App\Models\WeiboUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class WeiboUserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\WeiboUser';

    public function update($id)
    {
        $data = \request()->all();

        if (isset($data['pause'])) {
            $data['pause'] = $data['pause'] == 'on' ? 1 : 0;
        } else {
            return parent::update($id);
        }

        $weiboUser = WeiboUser::find($id);
        $weiboUser->fill($data);
        $weiboUser->save();
        return [
            'status'  => true,
            'message' => '更新成功.'
        ];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeiboUser);
        $grid->column('username', __('Username'));

        // 设置text、color、和存储值
        $states = [
            'off' => ['text' => '打开', 'color' => 'primary'],
            'on'  => ['text' => '关闭', 'color' => 'default'],
        ];
        $grid->column('pause', '推送')->switch($states);
        $grid->column('limit', __('Limit'));
        $grid->column('created_at', __('Created at'));
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(WeiboUser::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('password', __('Password'));
        $show->field('pause', __('Pause'));
        $show->field('limit', __('Limit'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WeiboUser);

        $form->text('username', __('Username'));
        $form->password('password', __('Password'));
        $form->number('limit', __('Limit'))->default(20);

        $form->saving(function (Form $form) {
            $form->password = bcrypt($form->password);
        });

        return $form;
    }
}
