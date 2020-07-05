<?php

namespace App\Admin\Controllers;

use \App\Models\Consultant;
use App\models\CrmGrabLog;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ConsultantController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '咨询管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Consultant);
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->column(1 / 2, function (Grid\Filter $filter) {


                $filter->like('name', __('Name'));
                $filter->like('keyword', __('匹配词'));

            });
            $filter->column(1 / 2, function (Grid\Filter $filter) {
                $filter->equal('type', __('Hospital type'))->select(CrmGrabLog::$typeList);
            });
        });
        $grid->disableRowSelector();

        $grid->column('type', __('Type'))->using(CrmGrabLog::$typeList);
        $grid->column('name', __('Name'));
        static::keywordLabelModal($grid, 'keyword', __('咨询员匹配词'));
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
        $show = new Show(Consultant::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('crm_id', __('Crm id'));
        $show->field('name', __('Name'));
        $show->field('type', __('Type'));
        $show->field('department_id', __('Department id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('keyword', __('Keyword'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Consultant);

//        $form->ignore(['type', 'name']);
        $form->select('type', __('Type'))->options(CrmGrabLog::$typeList)->readOnly();
        $form->text('name', __('Name'))->readOnly();
        $form->tags('keyword', __('Keyword'));

        return $form;
    }
}
