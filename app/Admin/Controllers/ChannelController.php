<?php

namespace App\Admin\Controllers;

use App\Models\Channel;
use App\Models\MediumType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class ChannelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\Channel';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Channel);

        $grid->column('title', __('Title'));
        $grid->column('mediums', '关联媒介')->pluck('title')->label();
        $grid->column('form_type', '关联数据类型')->display(function ($value) {
            return collect(explode(',', $value))->map(function ($value) {
                if (!$value) {
                    return '-无-';
                }
                return isset(Channel::$FormTypeList[$value]) ? Channel::$FormTypeList[$value] : '-未知-';
            })->toArray();
        })->label();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Channel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
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
        $form = new Form(new Channel);

        $form->text('title', __('Title'));
        $form->multipleSelect('mediums', '关联媒介')
            ->options(MediumType::select('id', 'title')->get()->pluck('title', 'id'))
            ->required();

        $form->multipleSelect('form_type', '表单关联')
            ->options([
                1 => '百度信息流',
                2 => '微博',
                4 => '抖音',
                3 => '头条',
            ])
            ->required();

        return $form;
    }


}
