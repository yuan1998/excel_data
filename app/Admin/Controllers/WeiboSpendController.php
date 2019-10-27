<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Models\WeiboSpend;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WeiboSpendController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\WeiboSpend';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeiboSpend);


        $type = $this->appendDataType($grid);

        $grid->tools(function (Grid\Tools $tools) use ($type) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExcelUpload($type ?? 'zx', 'weiboSpend'));
        });
        $grid->disableActions();
        $grid->disableCreateButton();


        $grid->column('date', __('Date'));
        $grid->column('advertiser_account', __('Advertiser account'));
        $grid->column('show', __('Show'));
        $grid->column('show_fans', __('Show fans'));
        $grid->column('interactive', __('Interactive'));
        $grid->column('spend', __('Spend'));
        $grid->column('spend_fans', __('Spend fans'));
        $grid->column('type', __('Type'))
            ->using(['zx' => '整形', 'kq' => '口腔']);

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
        $show = new Show(WeiboSpend::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('date', __('Date'));
        $show->field('advertiser_account', __('Advertiser account'));
        $show->field('show', __('Show'));
        $show->field('show_fans', __('Show fans'));
        $show->field('interactive', __('Interactive'));
        $show->field('interactive_fans', __('Interactive fans'));
        $show->field('interactive_rate', __('Interactive rate'));
        $show->field('interactive_rate_fans', __('Interactive rate fans'));
        $show->field('spend', __('Spend'));
        $show->field('spend_fans', __('Spend fans'));
        $show->field('thousand_times_show_price', __('Thousand times show price'));
        $show->field('thousand_times_show_price_fans', __('Thousand times show price fans'));
        $show->field('once_interactive_price', __('Once interactive price'));
        $show->field('once_interactive_price_fans', __('Once interactive price fans'));
        $show->field('quality_score', __('Quality score'));
        $show->field('quality_score_fans', __('Quality score fans'));
        $show->field('negative', __('Negative'));
        $show->field('negative_fans', __('Negative fans'));
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
        $form = new Form(new WeiboSpend);

        $form->text('date', __('Date'));
        $form->text('advertiser_account', __('Advertiser account'));
        $form->text('show', __('Show'));
        $form->text('show_fans', __('Show fans'));
        $form->text('interactive', __('Interactive'));
        $form->text('interactive_fans', __('Interactive fans'));
        $form->text('interactive_rate', __('Interactive rate'));
        $form->text('interactive_rate_fans', __('Interactive rate fans'));
        $form->text('spend', __('Spend'));
        $form->text('spend_fans', __('Spend fans'));
        $form->text('thousand_times_show_price', __('Thousand times show price'));
        $form->text('thousand_times_show_price_fans', __('Thousand times show price fans'));
        $form->text('once_interactive_price', __('Once interactive price'));
        $form->text('once_interactive_price_fans', __('Once interactive price fans'));
        $form->text('quality_score', __('Quality score'));
        $form->text('quality_score_fans', __('Quality score fans'));
        $form->text('negative', __('Negative'));
        $form->text('negative_fans', __('Negative fans'));
        $form->text('type', __('Type'))->default('zx');

        return $form;
    }
}
