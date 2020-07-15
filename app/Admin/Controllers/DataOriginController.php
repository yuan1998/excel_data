<?php

namespace App\Admin\Controllers;

use App\Models\Channel;
use \App\Models\DataOrigin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Arr;

class DataOriginController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '数据源管理';

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        static::initVue();

        $dataType             = json_encode(DataOrigin::$typeList);
        $typePropertyField = json_encode(DataOrigin::$typePropertyField);
        $requireProperty = json_encode(DataOrigin::$requireTypeProperty);
        $channelOptions       = Channel::all()->pluck('title', 'id');
        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body("<data-origin-create :channel-options='$channelOptions' :data-type='{$dataType}' :type-property-field='{$typePropertyField}' :require-property='{$requireProperty}'></data-origin-create>");
    }

    public function edit($id, Content $content)
    {
        static::initVue();

        $item                 = DataOrigin::find($id);
        $item['channel_id']   = $item->channels()->pluck('id');
        $dataType             = json_encode(DataOrigin::$typeList);
        $typePropertyField = json_encode(DataOrigin::$typePropertyField);
        $requireProperty = json_encode(DataOrigin::$requireTypeProperty);
        $channelOptions       = Channel::all()->pluck('title', 'id');

        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body("<data-origin-create :channel-options='$channelOptions' :data-type='{$dataType}' :model-item='{$item}' :type-property-field='{$typePropertyField}' :require-property='{$requireProperty}'></data-origin-create>");
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DataOrigin);
        $grid->model()
            ->with(['channels']);

        $grid->column('title', __('Title'));
        $grid->column('property_field', __('数据类型关联表头'))
            ->display(function () {
                $type = $this->data_type;
                return Arr::get(DataOrigin::$typeList, $type, $type);
            })
            ->modal('用于获取上传Excel文件数据的表头列表', function ($model) {
                $labels = $model->property_field;
                $string = collect($labels)
                    ->map(function ($label, $key) {

                        $labelText = collect($label)->map(function ($item) {
                            return "<h4 style='display: inline-block;margin-right:5px;'><span class=\" label label-success\">$item</span></h4>";
                        })->join('<span>+ </span>');

                        $name = Arr::get(DataOrigin::$fieldText, $key, $key);
                        return "<div> <h4>{$name} :</h4> {$labelText}</div>";

                    })
                    ->join("");
                return $string;
            });
        $grid->column('data_field', __('验证表头'))
            ->display(function ($val) {
                $count = count($val);
                return '共有' . $count . '个表头';
            })
            ->modal('用于验证上传Excel文件的表头列表', function ($model) {
                $labels = $model->data_field;
                $string = collect($labels)
                    ->map(function ($label) {
                        return "<h4 style='display: inline-block;margin-right:5px;'><span class=\" label label-success\">$label</span></h4>";
                    })
                    ->join("");
                return $string;
            });
        $grid->column('channels', __('关联渠道'))
            ->display(function ($val) {

                $count = count($this->channels);
                return '共关联' . $count . '个渠道';
            })
            ->modal('关联的渠道数据(Excel验证通过之后,只会判断关联的渠道)', function ($model) {
                $labels = $model->channels->pluck('title');
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
        $show = new Show(DataOrigin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('data_type', __('Data type'));
        $show->field('property_field', __('Property field'));
        $show->field('data_field', __('Data field'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DataOrigin);

        $form->text('title', __('Title'));
        $form->text('data_type', __('Data type'));
        $form->text('property_field', __('Property field'));
        $form->text('data_field', __('Data field'));

        return $form;
    }
}
