<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\CrmGrabData;
use App\Admin\Actions\ExportDataAction;
use App\Models\ExportDataLog;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Tools\BatchActions;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;

class ExportDataLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\ExportDataLog';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ExportDataLog);

        $grid->model()->orderBy('id', 'desc');
        $this->initVue();

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (BatchActions $batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExportDataAction());
        });

        $grid->actions(function ( $actions) {
            // 去掉编辑
            $actions->disableEdit();

            // 去掉查看
            $actions->disableView();
        });

        $grid->disableCreateButton();

        $grid->column('created_at', __('Created at'));
        $grid->column('full_path' , '文件名称')->display(function () {
            return Storage::disk('public')->url($this->path . $this->file_name);
        })->downloadable();
        $grid->column('status', __('Status'))->using(ExportDataLog::$staticList)->label();

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
        $show->field('file_name', __('File name'));
        $show->field('path', __('Path'));
        $show->field('status', __('Status'));
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
        $form = new Form(new ExportDataLog);

        $form->text('file_name', __('File name'));
        $form->text('path', __('Path'));
        $form->number('status', __('Status'));

        return $form;
    }
}
