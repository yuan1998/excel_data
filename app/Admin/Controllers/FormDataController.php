<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Models\DepartmentType;
use App\Models\FormData;
use App\Models\ProjectType;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FormDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\FormData';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FormData);
        $grid->model()->with(['phones', 'projects', 'department'])->orderBy('date', 'desc');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->column(6, function (Grid\Filter $filter) {
                $departmentOptions = DepartmentType::all()->pluck('title', 'id');
                $filter->equal('department_id', '科室')->radio($departmentOptions);

                $filter->between('date', '日期')->date();
            });
            $filter->column(6, function (Grid\Filter $filter) {
                $filter->where(function ($query) {

                    if (is_numeric($this->input)) {
                        $query->has('projects', '=', $this->input);
                    }
                }, '病种数')->select([
                    0 => '没有',
                    1 => '一个',
                    2 => '二个',
                ]);

            });
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->expand();
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExcelUpload());
        });
        $grid->disableCreateButton();
        $this->appendFormType($grid);

        $grid->column('department.title', __('科室'));
        $grid->column('form_type', __('Form type'))->using(FormData::$FormTypeList);
        $grid->column('phones', __('Phone'))->pluck('phone')->label();
        $grid->column('projects', __('Project'))->pluck('title')->label();
        $grid->column('date', __('Date'));
        $grid->column('data_type', __('表单自带类型'));

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
        $show = new Show(FormData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('weibo_id', __('Weibo id'));
        $show->field('baidu_id', __('Baidu id'));
        $show->field('feiyu_id', __('Feiyu id'));
        $show->field('archive_type', __('Archive type'));
        $show->field('form_type', __('Form type'));
        $show->field('date', __('Date'));
        $show->field('data_type', __('Data type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('type', __('Type'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FormData);

        $form->multipleSelect('projects', __('Project'))->options(ProjectType::all()->pluck('title', 'id'));
        $form->text('data_type', __('Data type'));

        return $form;
    }
}
