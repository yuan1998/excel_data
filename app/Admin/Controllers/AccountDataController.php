<?php

namespace App\Admin\Controllers;

use App\Models\AccountData;
use App\Models\Channel;
use App\models\CrmGrabLog;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AccountDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '账户';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AccountData);
        $grid->model()->with(['channel']);

        $type = $this->appendChannelType($grid, 'channel_id');

        $grid->column('type', __('分类'))
            ->using(CrmGrabLog::$typeList)
            ->label();

        if (!$type || $type == 'all') {
            $grid->column('channel.title', __('渠道名称'))->label();
        }

        $grid->column('name', __('账户名称'));
        $grid->column('rebate', __('Rebate'));
        $grid->column('keyword', __('匹配词'))
            ->display(function ($val) {
                return $val ? explode(',', $val) : [];
            })->label();
        $grid->column('crm_keyword', __('Crm 匹配词'))
            ->display(function ($val) {
                return $val ? explode(',', $val) : [];
            })->label();
        $grid->column('is_default', __('默认'))->bool();
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
        $show = new Show(AccountData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('rebate', __('Rebate'));
        $show->field('keyword', __('Keyword'));
        $show->field('crm_keyword', __('Crm keyword'));
        $show->field('channel_id', __('Channel id'));
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
        $form = new Form(new AccountData);

        $channelOptions = Channel::all()->pluck('title', 'id');
        $form->select('type', __('Type'))
            ->options(CrmGrabLog::$typeList)
            ->default('zx')
            ->required();
        $form->select('channel_id', __('所属渠道'))->options($channelOptions)->required();
        $form->text('name', __('账户名称'))->required();
        $form->text('rebate', __('Rebate'))->required();
        $form->tags('keyword', __('表单/消费匹配词'));
        $form->tags('crm_keyword', __('crm匹配词'));
        $form->switch('is_default', '设为默认')->options([
            0 => '关',
            1 => '开',
        ]);


        return $form;
    }
}
