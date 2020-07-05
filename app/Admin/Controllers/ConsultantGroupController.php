<?php

namespace App\Admin\Controllers;

use App\Models\Consultant;
use \App\Models\ConsultantGroup;
use App\models\CrmGrabLog;
use App\Models\FormData;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ConsultantGroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客服分组';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ConsultantGroup);

        $grid->model()->with(['consultants']);

        $grid->column('type', __('Type'))->using(CrmGrabLog::$typeList);
        $grid->column('title', __('Title'));

        $grid->column('consultants', __('咨询列表'))
            ->display(function ($val) {
                $labels = $this->consultants->pluck('name');
                $count  = count($labels);
                return '共有' . $count . '个咨询';
            })
            ->modal('关联咨询' . '-列表', function ($model) {
                $labels = $model->consultants->pluck('name');
                $string = collect($labels)
                    ->map(function ($label) {
                        return "<h4 style='display: inline-block;margin-right:5px;'><span class=\" label label-success\">$label</span></h4>";
                    })
                    ->join("");
                return $string;
            });

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
        $show = new Show(ConsultantGroup::findOrFail($id));

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
        $form = new Form(new ConsultantGroup);

        $form->radio('type', __('Type'))
            ->options(CrmGrabLog::$typeList);
        $form->text('title', __('Title'));
        $consultantOptions = Consultant::all()->pluck('name', 'id');
        $form->multipleSelect('consultants')->options($consultantOptions);

        return $form;
    }
}
