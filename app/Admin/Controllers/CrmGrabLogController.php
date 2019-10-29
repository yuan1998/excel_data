<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\CrmGrabData;
use App\Admin\Actions\ExcelUpload;
use App\Models\CrmGrabLog;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Tools\BatchActions;
use Encore\Admin\Show;

class CrmGrabLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\CrmGrabLog';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CrmGrabLog);
        $grid->model()->orderBy('id', 'desc');
        $this->initVue();

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (BatchActions $batch) {
                $batch->disableDelete();
            });

            $tools->append(new CrmGrabData());
        });

        $grid->disableCreateButton();

        $grid->column('name', __('Name'));
        $grid->column('type', __('Type'))->using(CrmGrabLog::$typeList);
        $grid->column('model_type', __('Model type'))->using(CrmGrabLog::$modelTypeList)->label();
        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));
        $grid->column('created_at', __('Created at'));
        $grid->column('status', __('Status'))->using(CrmGrabLog::$statusList)->label();

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
        $show = new Show(CrmGrabLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('type', __('Type'));
        $show->field('model_type', __('Model type'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
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
        $form = new Form(new CrmGrabLog);

        $form->text('name', __('Name'));
        $form->text('type', __('Type'));
        $form->text('model_type', __('Model type'));
        $form->text('start_date', __('Start date'));
        $form->text('end_date', __('End date'));
        $form->number('status', __('Status'));

        return $form;
    }
}
