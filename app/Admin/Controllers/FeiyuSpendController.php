<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Models\BaiduSpend;
use App\Models\FeiyuSpend;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FeiyuSpendController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\FeiyuSpend';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FeiyuSpend);


        $type = $this->appendDataType($grid);

        $grid->tools(function (Grid\Tools $tools) use ($type) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExcelUpload($type ?? 'zx', 'feiyuSpend'));
        });
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->column('date', __('Date'));
        $grid->column('advertiser_id', __('Advertiser id'));
        $grid->column('advertiser_name', __('Advertiser name'));
        $grid->column('show', __('Show'));
        $grid->column('click', __('Click'));
        $grid->column('spend', __('Spend'));
        $grid->column('conversion', __('Conversion'));
        $grid->column('deep_conversion', __('Deep conversion'));
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
        $show = new Show(FeiyuSpend::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('date', __('Date'));
        $show->field('advertiser_id', __('Advertiser id'));
        $show->field('advertiser_name', __('Advertiser name'));
        $show->field('show', __('Show'));
        $show->field('click', __('Click'));
        $show->field('click_rate', __('Click rate'));
        $show->field('average_click_price', __('Average click price'));
        $show->field('average_thousand_times_show_price', __('Average thousand times show price'));
        $show->field('spend', __('Spend'));
        $show->field('conversion', __('Conversion'));
        $show->field('conversion_price', __('Conversion price'));
        $show->field('conversion_rate', __('Conversion rate'));
        $show->field('deep_conversion', __('Deep conversion'));
        $show->field('deep_conversion_price', __('Deep conversion price'));
        $show->field('deep_conversion_rate', __('Deep conversion rate'));
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
        $form = new Form(new FeiyuSpend);

        $form->text('date', __('Date'));
        $form->text('advertiser_id', __('Advertiser id'));
        $form->text('advertiser_name', __('Advertiser name'));
        $form->text('show', __('Show'));
        $form->text('click', __('Click'));
        $form->text('click_rate', __('Click rate'));
        $form->text('average_click_price', __('Average click price'));
        $form->text('average_thousand_times_show_price', __('Average thousand times show price'));
        $form->text('spend', __('Spend'));
        $form->text('conversion', __('Conversion'));
        $form->text('conversion_price', __('Conversion price'));
        $form->text('conversion_rate', __('Conversion rate'));
        $form->text('deep_conversion', __('Deep conversion'));
        $form->text('deep_conversion_price', __('Deep conversion price'));
        $form->text('deep_conversion_rate', __('Deep conversion rate'));
        $form->text('type', __('Type'))->default('zx');

        return $form;
    }
}
