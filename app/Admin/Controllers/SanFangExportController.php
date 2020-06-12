<?php

namespace App\Admin\Controllers;

use \App\Models\ExportDataLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SanFangExportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '\App\Models\ExportDataLog';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ExportDataLog);

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('file_name', __('File name'));
        $grid->column('path', __('Path'));
        $grid->column('request_data', __('Request data'));
        $grid->column('status', __('Status'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('run_time', __('Run time'));
        $grid->column('data_type', __('Data type'));

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
        $show = new Show(ExportDataLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('file_name', __('File name'));
        $show->field('path', __('Path'));
        $show->field('request_data', __('Request data'));
        $show->field('status', __('Status'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('run_time', __('Run time'));
        $show->field('data_type', __('Data type'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ExportDataLog);

        $form->text('name', __('Name'));
        $form->text('file_name', __('File name'));
        $form->text('path', __('Path'));
        $form->text('request_data', __('Request data'));
        $form->number('status', __('Status'));
        $form->text('run_time', __('Run time'));
        $form->text('data_type', __('Data type'));

        return $form;
    }
}
