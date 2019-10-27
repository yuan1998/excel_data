<?php

namespace App\Admin\Controllers;

use App\Helpers;
use App\Models\AccountReturnPoint;
use App\Models\FormData;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AccountReturnPointController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\AccountReturnPoint';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AccountReturnPoint);
        $this->appendFormType($grid);

        $grid->column('name', __('Name'));
        $grid->column('rebate', __('Rebate'));
        $grid->column('keyword', __('Keyword'));
        $grid->column('form_type', __('Form type'))->using(FormData::$FormTypeList)->label();

        $grid->column('is_default', __('Is default'))->bool();
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
        $show = new Show(AccountReturnPoint::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('rebate', __('Rebate'));
        $show->field('keyword', __('Keyword'));
        $show->field('form_type', __('Form type'));
        $show->field('is_default', __('Is default'));
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
        $form = new Form(new AccountReturnPoint);

        $form->radio('form_type', __('Form type'))->options(FormData::$FormTypeList)
        ->required();

        $form->text('name','账户名称')->required();
        $form->text('rebate', __('Rebate'))->required();
        $form->text('keyword', __('Keyword'));
        $form->switch('is_default', __('Is default'));

        return $form;
    }
}
