<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Models\BaiduSpend;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BaiduSpendController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\BaiduSpend';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BaiduSpend);

        $type = $this->appendDataType($grid);

        $grid->tools(function (Grid\Tools $tools) use ($type) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExcelUpload($type ?? 'zx', 'baiduSpend'));
        });
        $grid->disableActions();
        $grid->disableCreateButton();


        $grid->column('date', __('Date'));
        $grid->column('promotion_plan', __('Promotion plan'));
        $grid->column('promotion_plan_id', __('Promotion plan id'));
        $grid->column('show', __('Show'));
        $grid->column('click', __('Click'));
        $grid->column('spend', __('Spend'));
        $grid->column('type', __('Type'))->using([
            'zx' => '整形',
            'kq' => '口腔'
        ]);

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
        $show = new Show(BaiduSpend::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('date', __('Date'));
        $show->field('promotion_plan', __('Promotion plan'));
        $show->field('promotion_plan_id', __('Promotion plan id'));
        $show->field('show', __('Show'));
        $show->field('click', __('Click'));
        $show->field('spend', __('Spend'));
        $show->field('type', __('Type'));
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
        $form = new Form(new BaiduSpend);

        $form->text('date', __('Date'));
        $form->text('promotion_plan', __('Promotion plan'));
        $form->text('promotion_plan_id', __('Promotion plan id'));
        $form->text('show', __('Show'));
        $form->text('click', __('Click'));
        $form->text('spend', __('Spend'));
        $form->text('type', __('Type'))->default('zx');

        return $form;
    }
}
