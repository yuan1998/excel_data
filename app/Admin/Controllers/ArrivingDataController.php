<?php

namespace App\Admin\Controllers;

use App\Models\ArchiveType;
use App\Models\ArrivingData;
use App\Models\Channel;
use App\Models\DepartmentType;
use App\Models\MediumType;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArrivingDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '到院数据';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ArrivingData);

        $grid->model()->with(['customerPhone', 'projects'])->orderBy('reception_date', 'desc');
        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {

            $filter->column(6, function (Grid\Filter $filter) {

                $channelOptions    = Channel::all()->pluck('title', 'id');
                $departmentOptions = DepartmentType::all()->pluck('title', 'id');

                $filter->where(function ($query) {
                    $val = $this->input;
                    if ($val) {
                        $channel = Channel::with(['mediums'])->find($val);
                        if ($channel) {
                            $mediumId = $channel->mediums->pluck('id');
                            $query->whereIn('medium_id', $mediumId);
                        }
                    }
                }, '渠道')->select($channelOptions);

                $filter->where(function ($query) {
                    $val = $this->input;

                    if ($val && $department = DepartmentType::with(['archives'])->find($val)) {
                        $archiveId = $department->archives->pluck('id');
                        $query->whereIn('archive_id', $archiveId);
                    }
                }, '科室')->select($departmentOptions);

                $filter->betweenDate('reception_date', '日期')->date();
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


//        $grid->header(function ($query) {
//
//            return new Box('123', '123');
//        });

        $grid->column('customer', __('Customer'))
            ->style("white-space: nowrap;");
        $grid->column('is_transaction', __('Is transaction'))
            ->style("white-space: nowrap;")
            ->bool([' 是 ' => true, ' 否 ' => false]);
        $grid->column('customer_status', __('Customer status'))
            ->style("white-space: nowrap;");
        $grid->column('again_arriving', __('Again arriving'))
            ->bool(['二次' => true, '首次' => false])
            ->style("white-space: nowrap;");

        $grid->column('projects', __("Project"))->pluck('title')->label();

        $grid->column('phone', __('Phone'))
            ->display(function ($value) {
                return $value ?? $this->phone;
            })
            ->label();

        $grid->column('online_archive_by', __('Online archive by'))
            ->style("white-space: nowrap;");
        $grid->column('medium', __('Medium'))
            ->style("white-space: nowrap;");
        $grid->column('archive_type', __('Archive type'))
            ->style("white-space: nowrap;");
        $grid->column('temp_archive_date', __('Temp archive date'))
            ->style("white-space: nowrap;");
        $grid->column('archive_by', __('Archive by'))
            ->style("white-space: nowrap;");
        $grid->column('reception_date', __('Reception date'))
            ->style("white-space: nowrap;");
        $grid->column('real_payment', __('Real payment'))
            ->style("white-space: nowrap;");
        $grid->column('payable', __('Payable'))
            ->style("white-space: nowrap;");
        $grid->column('order_type', __('Order type'))
            ->style("white-space: nowrap;");
        $grid->column('pay_date', __('Pay date'))
            ->style("white-space: nowrap;");

        $grid->column('intention', __('Intention'))
            ->style("white-space: nowrap;");

        $grid->column('type', __('Type'))
            ->using(['zx' => '整形', 'kq' => '口腔'])
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
        $show = new Show(ArrivingData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('is_transaction', __('Is transaction'));
        $show->field('customer_status', __('Customer status'));
        $show->field('again_arriving', __('Again arriving'));
        $show->field('customer', __('Customer'));
        $show->field('phone', __('Phone'));
        $show->field('gender', __('Gender'));
        $show->field('age', __('Age'));
        $show->field('visitor_id', __('Visitor id'));
        $show->field('project_demand', __('Project demand'));
        $show->field('online_customer', __('Online customer'));
        $show->field('online_archive_by', __('Online archive by'));
        $show->field('medium', __('Medium'));
        $show->field('archive_type', __('Archive type'));
        $show->field('beauty_salon_type', __('Beauty salon type'));
        $show->field('beauty_salon_name', __('Beauty salon name'));
        $show->field('online_return_visit_by', __('Online return visit by'));
        $show->field('doctor', __('Doctor'));
        $show->field('temp_archive_date', __('Temp archive date'));
        $show->field('archive_by', __('Archive by'));
        $show->field('reception_date', __('Reception date'));
        $show->field('real_payment', __('Real payment'));
        $show->field('payable', __('Payable'));
        $show->field('order_type', __('Order type'));
        $show->field('pay_date', __('Pay date'));
        $show->field('reception_form_number', __('Reception form number'));
        $show->field('order_form_number', __('Order form number'));
        $show->field('reservation_form_number', __('Reservation form number'));
        $show->field('intention', __('Intention'));
        $show->field('department', __('Department'));
        $show->field('reservation_expert', __('Reservation expert'));
        $show->field('referrer_by', __('Referrer by'));
        $show->field('referrer_relation', __('Referrer relation'));
        $show->field('customer_card_number', __('Customer card number'));
        $show->field('qq', __('Qq'));
        $show->field('weixin', __('Weixin'));
        $show->field('province', __('Province'));
        $show->field('city', __('City'));
        $show->field('staff_referrer', __('Staff referrer'));
        $show->field('comment', __('Comment'));
        $show->field('uuid', __('Uuid'));
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
        $form = new Form(new ArrivingData);

        $form->text('customer_id', __('Customer id'));
        $form->text('is_transaction', __('Is transaction'));
        $form->text('customer_status', __('Customer status'));
        $form->text('again_arriving', __('Again arriving'));
        $form->text('customer', __('Customer'));
        $form->mobile('phone', __('Phone'));
        $form->text('gender', __('Gender'));
        $form->text('age', __('Age'));
        $form->text('visitor_id', __('Visitor id'));
        $form->text('project_demand', __('Project demand'));
        $form->text('online_customer', __('Online customer'));
        $form->text('online_archive_by', __('Online archive by'));
        $form->text('medium', __('Medium'));
        $form->text('archive_type', __('Archive type'));
        $form->text('beauty_salon_type', __('Beauty salon type'));
        $form->text('beauty_salon_name', __('Beauty salon name'));
        $form->text('online_return_visit_by', __('Online return visit by'));
        $form->text('doctor', __('Doctor'));
        $form->text('temp_archive_date', __('Temp archive date'));
        $form->text('archive_by', __('Archive by'));
        $form->text('reception_date', __('Reception date'));
        $form->text('real_payment', __('Real payment'));
        $form->text('payable', __('Payable'));
        $form->text('order_type', __('Order type'));
        $form->text('pay_date', __('Pay date'));
        $form->text('reception_form_number', __('Reception form number'));
        $form->text('order_form_number', __('Order form number'));
        $form->text('reservation_form_number', __('Reservation form number'));
        $form->text('intention', __('Intention'));
        $form->text('department', __('Department'));
        $form->text('reservation_expert', __('Reservation expert'));
        $form->text('referrer_by', __('Referrer by'));
        $form->text('referrer_relation', __('Referrer relation'));
        $form->text('customer_card_number', __('Customer card number'));
        $form->text('qq', __('Qq'));
        $form->text('weixin', __('Weixin'));
        $form->text('province', __('Province'));
        $form->text('city', __('City'));
        $form->text('staff_referrer', __('Staff referrer'));
        $form->textarea('comment', __('Comment'));
        $form->text('uuid', __('Uuid'));
        $form->text('type', __('Type'))->default('zx');

        return $form;
    }
}
