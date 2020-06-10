<?php

namespace App\Admin\Controllers;

use \App\Models\FormTypeList;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FormTypeListController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '数据类型管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FormTypeList);
        $grid->disableRowSelector();
        $grid->disableCreateButton();

        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
            $create->text('name', '名称');
        });

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
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
        $show = new Show(FormTypeList::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
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
        $form = new Form(new FormTypeList);

        $form->text('name', __('Name'));

        return $form;
    }
}
