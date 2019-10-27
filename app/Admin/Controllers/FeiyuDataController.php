<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Clients\BaseClient;
use App\Models\FeiyuData;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class FeiyuDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\FeiyuData';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FeiyuData);
        $type = $type = $this->appendDataType($grid);

        $grid->tools(function (Grid\Tools $tools) use ($type) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExcelUpload($type ?? 'zx', 'feiyu'));
        });
        $grid->disableActions();
        $grid->disableCreateButton();


        $grid->column('is_archive', __('Is archive'))->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->column('intention', __('Intention'))->display(function ($value) {
            return BaseClient::$intention_list[$value];
        });
        $grid->column('name', __('Name'));
        $grid->column('phone', __('Phone'));
        $grid->column('source', __('Source'));
        $grid->column('component_name', __('Component name'));
        $grid->column('activity_name', __('Activity name'));
        $grid->column('post_date', __('Post date'));


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
        $show = new Show(FeiyuData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('clue_id', __('Clue id'));
        $show->field('name', __('Name'));
        $show->field('phone', __('Phone'));
        $show->field('clue_status', __('Clue status'));
        $show->field('owner', __('Owner'));
        $show->field('call_status', __('Call status'));
        $show->field('tag', __('Tag'));
        $show->field('clue_type', __('Clue type'));
        $show->field('source', __('Source'));
        $show->field('conversion_status', __('Conversion status'));
        $show->field('sponsored_link', __('Sponsored link'));
        $show->field('weixin', __('Weixin'));
        $show->field('qq', __('Qq'));
        $show->field('email', __('Email'));
        $show->field('gender', __('Gender'));
        $show->field('age', __('Age'));
        $show->field('date', __('Date'));
        $show->field('city', __('City'));
        $show->field('address', __('Address'));
        $show->field('component_id', __('Component id'));
        $show->field('component_name', __('Component name'));
        $show->field('activity_id', __('Activity id'));
        $show->field('activity_name', __('Activity name'));
        $show->field('remarks', __('Remarks'));
        $show->field('comment', __('Comment'));
        $show->field('follow_logs', __('Follow logs'));
        $show->field('post_date', __('Post date'));
        $show->field('advertiser_id', __('Advertiser id'));
        $show->field('advertiser_name', __('Advertiser name'));
        $show->field('location', __('Location'));
        $show->field('store_id', __('Store id'));
        $show->field('store_name', __('Store name'));
        $show->field('type', __('Type'));
        $show->field('intention', __('Intention'));
        $show->field('is_archive', __('Is archive'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('arriving_type', __('Arriving type'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FeiyuData);

        $form->text('clue_id', __('Clue id'));
        $form->text('name', __('Name'));
        $form->mobile('phone', __('Phone'));
        $form->text('clue_status', __('Clue status'));
        $form->text('owner', __('Owner'));
        $form->text('call_status', __('Call status'));
        $form->text('tag', __('Tag'));
        $form->text('clue_type', __('Clue type'));
        $form->text('source', __('Source'));
        $form->text('conversion_status', __('Conversion status'));
        $form->text('sponsored_link', __('Sponsored link'));
        $form->text('weixin', __('Weixin'));
        $form->text('qq', __('Qq'));
        $form->email('email', __('Email'));
        $form->text('gender', __('Gender'));
        $form->text('age', __('Age'));
        $form->text('date', __('Date'));
        $form->text('city', __('City'));
        $form->text('address', __('Address'));
        $form->text('component_id', __('Component id'));
        $form->text('component_name', __('Component name'));
        $form->text('activity_id', __('Activity id'));
        $form->text('activity_name', __('Activity name'));
        $form->text('remarks', __('Remarks'));
        $form->text('comment', __('Comment'));
        $form->text('follow_logs', __('Follow logs'));
        $form->text('post_date', __('Post date'));
        $form->text('advertiser_id', __('Advertiser id'));
        $form->text('advertiser_name', __('Advertiser name'));
        $form->text('location', __('Location'));
        $form->text('store_id', __('Store id'));
        $form->text('store_name', __('Store name'));
        $form->text('type', __('Type'))->default('zx');
        $form->number('intention', __('Intention'));
        $form->switch('is_archive', __('Is archive'));
        $form->number('arriving_type', __('Arriving type'));

        return $form;
    }
}
