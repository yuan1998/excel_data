<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Models\AccountData;
use App\Models\DepartmentType;
use App\Models\FormData;
use App\Models\FormDataPhone;
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
    protected $title = '表单数据';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $this->initVue();

        $grid = new Grid(new FormData);
        $grid->model()->with(['phones', 'projects', 'department', 'account'])->orderBy('date', 'desc');


        $grid->filter(function (Grid\Filter $filter) {
            $filter->column(6, function (Grid\Filter $filter) {
                $departmentOptions = DepartmentType::all()->pluck('title', 'id')->toArray();
                $departmentOptions = array_merge(["0" => '没有科室'], $departmentOptions);

                $filter->where(function ($query) {
                    if ($this->input) {
                        $query->where('department_id', $this->input);
                    } else {
                        $query->whereNull('department_id');
                    }
                }, '科室')->select($departmentOptions);


                $accountOptions = array_merge(
                    ['0' => '没有账户'],
                    AccountData::all()->pluck('name', 'id')->toArray()
                );
                $filter->where(function ($query) {
                    if ($this->input) {
                        $query->where('account_id', $this->input);
                    } else {
                        $query->whereNull('account_id');
                    }
                }, '账户')->select($accountOptions);


                $filter->where(function ($query) {
                    $val = $this->input;
                    if ($val >= 0) {
                        if ($val == 3) {
                            $query->whereHas('phones', function ($query) use ($val) {
                                $query->where('is_repeat', 2);
                            });
                        } else {
                            $query->whereHas('phones', function ($query) use ($val) {
                                $query->where('is_archive', $val);
                            });
                        }
                    }
                }, '建档状态')->select([
                    0 => '未查询',
                    1 => '已建档',
                    2 => '未建档',
                    3 => '重复建档',
                ]);


                $filter->between('date', '日期')->date();

            });
            $filter->column(6, function (Grid\Filter $filter) {
                $filter->equal('form_type', '表单类型')->select(FormData::$FormTypeList);

                $projectOption = ProjectType::all()->pluck('title', 'id')->toArray();
                $projectOption = array_merge(["0" => '其他'], $projectOption);
                $filter->where(function ($query) {
                    $id = $this->input;
                    if ($id) {
                        $query->whereHas('projects', function ($query) use ($id) {
                            $query->where('id', $id);
                        });
                    } else {
                        $query->doesntHave('projects');
                    }
                }, '病种')->select($projectOption);

            });
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->expand();
        });
        $grid->disableCreateButton();

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExcelUpload([
                'weibo'  => '微博表单',
                'feiyu'  => '飞鱼表单',
                'baidu'  => '快商通表单',
                'yiliao' => '易聊表单(只识别信息流)'
            ]));
        });

        $grid->column('department_info', __('科室'))->display(function () {
            return $this->department ? $this->department->title : '-';
        });
        $grid->column('form_type', __('Form type'))->using(FormData::$FormTypeList);
        $grid->column('phones', __('Phone'))->display(function ($val) {
            return collect($val)->map(function ($item) {
                return FormDataPhone::toString($item);
            });
        })->label();
        $grid->column('project_info', __('Project'))->display(function () {
            $project = $this->projects->first();
            return $project ? $project->title : '其他';
        })->label();
        $grid->column('account.name', '所属账户')->label();
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
