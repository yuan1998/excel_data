<?php

namespace App\Admin\Controllers;

use App\Models\ArchiveType;
use App\Models\DepartmentType;
use App\Models\ProjectType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DepartmentTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '科室';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DepartmentType);
        $grid->model()->with(['archives']);

        $grid->column('title', __('Title'));
        $grid->column('archives', __('关联建档类型'))->style('width:400px')->pluck('title')->label();
        $grid->column('keyword', __('匹配词'))->display(function ($val) {
            return $val ? explode(',', $val) : [];
        })->style('width:400px')->label();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
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
        $show = new Show(DepartmentType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
//        $show->field('projects', '关联项目')->pluck('title')->label();
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
        // "双眼皮,自体脂肪,脂肪,吸脂,减肥瘦身,溶脂针,隆鼻,鼻部,唇部整形,耳部整形,眉部整形,面部精雕,面部整形,下巴整形,私密,处女膜修复,阴道紧缩,隆胸,胸部整形,胸部修复,眼袋,祛眼袋"
        // "玻尿酸,埋线,线雕,美白针,除皱,肉毒素"
        // "疤痕,祛斑,祛痘,抗衰除皱,皱纹,妊娠纹,水光针,洗纹身,洗眉"
        $form = new Form(new DepartmentType);
        $data = ArchiveType::query()->select('id', 'title')
            ->get()
            ->pluck('title', 'id');

        $form->text('title', __('Title'));
        $form->select('type', __('Type'))
            ->options([
                'zx' => '整形',
                'kq' => '口腔',
            ])
            ->required();
        $form->tags('keyword', __('匹配词'))
            ->required();
        $form->listbox('archives', '关联项目')
            ->options($data)
            ->required();
//        $form->listbox('projects', '关联项目')->options($projectTypes);

        return $form;
    }
}
