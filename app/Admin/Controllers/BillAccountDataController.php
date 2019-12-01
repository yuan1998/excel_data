<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\DepartmentDataType;
use App\Models\ArchiveType;
use App\Models\BillAccountData;
use App\Models\Channel;
use App\Models\DepartmentType;
use App\Models\MediumType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BillAccountDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\BillAccountData';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BillAccountData);
        $grid->model()->with(['medium']);

        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->column(6, function (Grid\Filter $filter) {
                $channelOptions    = Channel::all()->pluck('title', 'id');
                $departmentOptions = DepartmentType::all()->pluck('title', 'id');

                $filter->where(function ($query) {
                    $val = $this->input;
                    if ($val && $channel = Channel::with(['mediums'])->find($val)) {
                        $mediumId = $channel->mediums->pluck('id');
                        $query->whereIn('medium_id', $mediumId);
                    }
                }, '渠道')->select($channelOptions);

                $filter->where(function ($query) {
                    $val = $this->input;

                    if ($val && $department = DepartmentType::with(['archives'])->find($val)) {
                        $archiveId = $department->archives->pluck('id');
                        $query->whereIn('archive_id', $archiveId);
                    }
                }, '科室')->select($departmentOptions);

                $filter->between('pay_date', '日期')->date();
            });
            $filter->column(6, function (Grid\Filter $filter) {
                $mediumOptions  = MediumType::all()->pluck('title', 'id');
                $archiveOptions = ArchiveType::all()->pluck('title', 'id');
                $filter->equal('type', '数据类型')
                    ->select([
                        'zx' => '整形',
                        'kq' => '口腔'
                    ]);

                $filter->in('medium_id', '媒介类型')
                    ->multipleSelect($mediumOptions);
                $filter->in('archive_id', '建档类型')
                    ->multipleSelect($archiveOptions);

            });

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();
        });


        $grid->column('type', __('Type'))
            ->using(['zx' => '整形', 'kq' => '口腔'])
            ->style("white-space: nowrap;")
            ->label();

        $grid->column('medium.title', __('媒介'))
            ->style("white-space: nowrap;");

        $grid->column('archive_type', __('Archive type'))
            ->label()
            ->style("white-space: nowrap;");

        $grid->column('online_customer', __('Online customer'))
            ->style("white-space: nowrap;");

        $grid->column('archive_by', __('Archive by'))
            ->style("white-space: nowrap;");


        $grid->column('online_return_visit_by', __('Online return visit by'))
            ->style("white-space: nowrap;");


        $grid->column('account_by', __('开单人'))
            ->style("white-space: nowrap;");

        $grid->column('order_type', __('Order type'))
            ->style("white-space: nowrap;");

        $grid->column('customer', __('Customer'))
            ->style("white-space: nowrap;");

        $grid->column('customer_status', __('Customer status'))
            ->style("white-space: nowrap;");

        $grid->column('again_arriving', __('Again arriving'))
            ->style("white-space: nowrap;");

        $grid->column('phone', __('Phone'))
            ->display(function ($value) {
                return $value ?? $this->phone;
            })
            ->label();

        $grid->column('pay_date', __('Pay date'))
            ->style("white-space: nowrap;");

        $grid->column('total', __('总金额'))
            ->style("white-space: nowrap;");

        $grid->column('payable', __('Payable'))
            ->style("white-space: nowrap;");

        $grid->column('real_payment', __('Real payment'))
            ->style("white-space: nowrap;");

        $grid->column('order_account', __('开单业绩'))
            ->style("white-space: nowrap;");


        $grid->column('visitor_id', __('Visitor id'));

        $grid->column('archive_date', __('Archive date'))
            ->style("white-space: nowrap;");


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
        $show = new Show(BillAccountData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('online_customer', __('Online customer'));
        $show->field('archive_by', __('Archive by'));
        $show->field('archive_type', __('Archive type'));
        $show->field('online_return_visit_by', __('Online return visit by'));
        $show->field('medium_type', __('Medium type'));
        $show->field('medium_source', __('Medium source'));
        $show->field('account_by', __('Account by'));
        $show->field('order_form_number', __('Order form number'));
        $show->field('order_type', __('Order type'));
        $show->field('customer', __('Customer'));
        $show->field('customer_status', __('Customer status'));
        $show->field('again_arriving', __('Again arriving'));
        $show->field('phone', __('Phone'));
        $show->field('customer_card_number', __('Customer card number'));
        $show->field('pay_date', __('Pay date'));
        $show->field('total', __('Total'));
        $show->field('payable', __('Payable'));
        $show->field('real_payment', __('Real payment'));
        $show->field('order_account', __('Order account'));
        $show->field('beauty_salon_type', __('Beauty salon type'));
        $show->field('beauty_salon_name', __('Beauty salon name'));
        $show->field('total_pay', __('Total pay'));
        $show->field('total_account', __('Total account'));
        $show->field('visitor_id', __('Visitor id'));
        $show->field('archive_date', __('Archive date'));
        $show->field('type', __('Type'));
        $show->field('uuid', __('Uuid'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('customer_id', __('Customer id'));
        $show->field('medium_id', __('Medium id'));
        $show->field('archive_id', __('Archive id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BillAccountData);

        $form->text('online_customer', __('Online customer'));
        $form->text('archive_by', __('Archive by'));
        $form->text('archive_type', __('Archive type'));
        $form->text('online_return_visit_by', __('Online return visit by'));
        $form->text('medium_type', __('Medium type'));
        $form->text('medium_source', __('Medium source'));
        $form->text('account_by', __('Account by'));
        $form->text('order_form_number', __('Order form number'));
        $form->text('order_type', __('Order type'));
        $form->text('customer', __('Customer'));
        $form->text('customer_status', __('Customer status'));
        $form->text('again_arriving', __('Again arriving'));
        $form->mobile('phone', __('Phone'));
        $form->text('customer_card_number', __('Customer card number'));
        $form->text('pay_date', __('Pay date'));
        $form->text('total', __('Total'));
        $form->text('payable', __('Payable'));
        $form->text('real_payment', __('Real payment'));
        $form->text('order_account', __('Order account'));
        $form->text('beauty_salon_type', __('Beauty salon type'));
        $form->text('beauty_salon_name', __('Beauty salon name'));
        $form->text('total_pay', __('Total pay'));
        $form->text('total_account', __('Total account'));
        $form->text('visitor_id', __('Visitor id'));
        $form->text('archive_date', __('Archive date'));
        $form->text('type', __('Type'))->default('zx');
        $form->text('uuid', __('Uuid'));
        $form->text('customer_id', __('Customer id'));
        $form->number('medium_id', __('Medium id'));
        $form->number('archive_id', __('Archive id'));

        return $form;
    }
}
