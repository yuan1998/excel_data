<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\SpendExcelUpload;
use App\Models\DepartmentType;
use App\Models\FormData;
use App\Models\ProjectType;
use App\Models\SpendData;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SpendDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\SpendData';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SpendData);
        $grid->model()->with(['projects', 'department'])->orderBy('date', 'desc');

        $grid->filter(function (Grid\Filter $filter) {

            $departmentOptions = DepartmentType::all()->pluck('title', 'id');
            $filter->equal('department_id', '科室')->radio($departmentOptions);
            // 设置datetime类型
            $filter->between('date', '日期')->date();
            $filter->where(function ($query) {

                if (is_numeric($this->input)) {
                    $query->has('projects', '=', $this->input);
                }
            }, '病种数')->select([
                0 => '没有',
                1 => '一个',
                2 => '二个',
            ]);

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();
        });


        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new SpendExcelUpload());
        });
        $grid->disableCreateButton();
        $this->appendFormType($grid, 'spend_type');

        $grid->column('department.title', __('科室'))->label();
        $grid->column('date', __('Date'));
        $grid->column('spend_type', __('消费类型'))->using(FormData::$FormTypeList)->label();
        $grid->column('projects', __('Project'))->pluck('title')->label();

        $grid->column('spend_name', __('Spend name'));
        $grid->column('spend', __('Spend'));
        $grid->column('show', __('Show'));
        $grid->column('click', __('Click'));

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
        $show = new Show(SpendData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('baidu_id', __('Baidu id'));
        $show->field('feiyu_id', __('Feiyu id'));
        $show->field('weibo_id', __('Weibo id'));
        $show->field('date', __('Date'));
        $show->field('spend', __('Spend'));
        $show->field('spend_type', __('Spend type'));
        $show->field('show', __('Show'));
        $show->field('click', __('Click'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SpendData);
        $form->multipleSelect('projects', __('Project'))->options(ProjectType::all()->pluck('title', 'id'));

        return $form;
    }
}
