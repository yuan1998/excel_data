<?php

namespace App\Admin\Controllers;

use App\Models\ArchiveType;
use App\Models\Channel;
use App\models\CrmGrabLog;
use App\Models\DepartmentType;
use App\Models\ProjectType;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;

class ProjectTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '科室-病种';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProjectType);
        $grid->model()->with(['archives', 'department']);

        $grid->disableRowSelector();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->column(1 / 2, function (Grid\Filter $filter) {
                $departmentOptions = DepartmentType::all()->pluck('title', 'id')->toArray();


                $filter->equal('type', __('Hospital type'))->select(CrmGrabLog::$typeList);
                $filter->equal('department_id', __('Department by'))->select($departmentOptions);

            });
            $filter->column(1 / 2, function (Grid\Filter $filter) {
                $filter->like('title', __('Department project name'));
            });
        });


        $grid->fixColumns(2);
        $grid->column('department.title', __('所属科室'))->label();

        $grid->column('title', __('Department project name'))->style("text-align:center;");

        static::keywordLabelModal($grid, 'keyword', __('表单数据匹配词'));
        static::keywordLabelModal($grid, 'spend_keyword', __('消费数据匹配词'));
//        static::keywordLabelModal($grid, 'archives', __('建档类型匹配'));

        $grid->column('archives', __('建档类型匹配'))
            ->display(function ($val) {
                $labels = $this->archives->pluck('title');
                $count  = count($labels);
                return '共有关联' . $count . '个建档类型';
            })->modal('建档类型匹配' . '-列表', function ($model) {
                $labels = $model->archives->pluck('title');
                $string = collect($labels)->map(function ($label) {
                    return "<h4 style='display: inline-block;margin-right:5px;'><span class=\" label label-success\">$label</span></h4>";
                })->join("");
                return $string;
            });

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
            ->options($departmentTypes)
            ->load(
                'archives',
                '/api/department/archives',
                $id
            )
            ->required();

        $form->multipleSelect('archives', __('建档类型匹配'))->required();
        $form->text('title', __('Title'))->required();
        $form->tags('keyword', __('表单数据匹配词'))->required();
        $form->tags('spend_keyword', __('消费数据匹配词'))->required();

        return $form;
    }
}
