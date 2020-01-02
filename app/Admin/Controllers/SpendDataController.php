<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Admin\Actions\SpendExcelUpload;
use App\Models\AccountData;
use App\models\CrmGrabLog;
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
    protected $title = '消费数据';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $this->initVue();
        $grid = new Grid(new SpendData);
        $grid->model()->with(['projects', 'department', 'account'])->orderBy('date', 'desc');

        $grid->filter(function (Grid\Filter $filter) {

            $filter->column(6, function (Grid\Filter $filter) {
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

                $departmentOptions = DepartmentType::all()->pluck('title', 'id')->toArray();
                $departmentOptions = array_merge(["0" => '没有科室'], $departmentOptions);

                $filter->where(function ($query) {
                    if ($this->input) {
                        $query->where('department_id', $this->input);
                    } else {
                        $query->whereNull('department_id');
                    }
                }, '科室')->select($departmentOptions);
                $filter->between('date', '日期')->date();
            });

            $filter->column(6, function (Grid\Filter $filter) {
                $filter->equal('spend_type', '消费类型')->select(FormData::$FormTypeList);
                $filter->equal('type', '数据类型')->select(CrmGrabLog::$typeList);

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

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

//            $tools->append(new ExcelUpload([
//                'weibo_spend' => '微博消费',
//                'feiyu_spend' => '飞鱼消费',
//                'baidu_spend' => '百度消费',
//                'oppo_spend'  => 'oppo消费',
//            ]));
        });
        $grid->disableCreateButton();

        $grid->column('department_info', __('科室'))->display(function () {
            return $this->department ? $this->department->title : '-';
        });
        $grid->column('date', __('Date'));
        $grid->column('spend_type', __('消费类型'))->using(FormData::$FormTypeList)->label();
        $grid->column('project_info', __('Project'))->display(function () {
            $project = $this->projects->first();
            return $project ? $project->title : '其他';
        })->label();
        $grid->column('account.name', __('账户名称'))->label();
        $grid->column('spend_name', __('Spend name'));
        $grid->column('spend', __('Spend'));
        $grid->column('show', __('Show'));
        $grid->column('click', __('Click'));
        $grid->column('created_at', __('上传时间'));

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
        $form->text('spend_name', __('消费名称'));
        $form->multipleSelect('projects', __('Project'))->options(ProjectType::all()->pluck('title', 'id'));
        $form->select('account_id', __('账户'))->options(AccountData::all()->pluck('name', 'id'));

        return $form;
    }
}
