<?php

namespace App\Admin\Controllers;

use App\Models\ArchiveType;
use App\Models\DepartmentType;
use App\Models\ProjectType;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Tree;

class ProjectTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\ProjectType';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProjectType);
        $grid->model()->with(['archives', 'department']);
        $this->appendDepartmentType($grid);

        $grid->column('department.title', __('所属科室'))->style("width:88px;");
        $grid->column('title', __('Title'))->style("width:88px;");
        $grid->column('keyword', __('表单数据匹配词'))->style('width:250px;')->display(function ($val) {
            return $val ? explode(',', $val) : [];
        })->label();
        $grid->column('spend_keyword', __('消费数据匹配词'))->style('width:250px;')->display(function ($val) {
            return $val ? explode(',', $val) : [];
        })->label();
        $grid->column('archives', __('建档类型匹配'))->style('width:350px;')->pluck('title')->label();
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
        $show = new Show(ProjectType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('type', __('Type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }


    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($id)->edit($id));
    }

    /**
     * Make a form builder.
     * @param $id
     *
     * @return Form
     */
    protected function form($id = null)
    {
        $form = new Form(new ProjectType);

        $departmentTypes = DepartmentType::all()->pluck('title', 'id');

        $form->mySelect('department_id', __('所属科室'))
            ->options($departmentTypes)->load(
                'archives',
                '/api/department/archives',
                $id
            )->required();
        $form->multipleSelect('archives', __('建档类型匹配'))->required();

        $form->text('title', __('Title'))->required();
        $form->tags('keyword', __('表单数据匹配词'))->required();
        $form->tags('spend_keyword', __('消费数据匹配词'))->required();

        return $form;
    }
}
