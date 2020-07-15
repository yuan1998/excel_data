<?php

namespace App\Admin\Controllers;

use App\Models\Channel;
use App\Models\FormData;
use App\Models\MediumType;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class ChannelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '平台渠道管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Channel);

        $grid->disableRowSelector();

        $grid->column('title', __('Title'));
        $grid->column('mediums', '关联媒介')
            ->display(function ($val) {
                $labels = $this->mediums->pluck('title');
                $count  = count($labels);
                return '共关联' . $count . '个媒介类型';
            })
            ->modal('关联媒介' . '-列表', function ($model) {
                $labels = $model->mediums->pluck('title');
                $string = collect($labels)->map(function ($label) {
                    return "<h4 style='display: inline-block;margin-right:5px;'><span class=\" label label-success\">$label</span></h4>";
                })->join("");
                return $string;
            });

//        $grid->column('form_type', '关联数据类型')
//            ->display(function ($value) {
//                return collect(explode(',', $value))->map(function ($value) {
//                    if (!$value) {
//                        return '-无-';
//                    }
//                    return isset(FormData::$FormTypeList[$value]) ? FormData::$FormTypeList[$value] : '-未知-';
//                })->toArray();
//            })
//            ->label();

        static::keywordLabelModal($grid, 'keyword', __('匹配词'));

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
        $show = new Show(Channel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
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
        $form = new Form(new Channel);

        $form->text('title', __('Title'));
        $form->multipleSelect('mediums', '关联媒介')
            ->options(MediumType::select('id', 'title')->get()->pluck('title', 'id'))
            ->required();

//        $form->multipleSelect('form_type', '表单关联')
//            ->options(FormData::$FormTypeList)
//            ->required();
        $form->tags('keyword', __('匹配词'));

        return $form;
    }


}
