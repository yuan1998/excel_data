<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Admin\Actions\SpendData\RecheckSpendItem;
use App\Admin\Actions\SpendExcelUpload;
use App\Models\AccountData;
use App\models\CrmGrabLog;
use App\Models\DepartmentType;
use App\Models\FormData;
use App\Models\ProjectType;
use App\Models\SpendData;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

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
        static::clearAutoComplete();
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
        });

        $grid->actions(function ($actions) {
            $actions->add(new RecheckSpendItem());
        });
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->fixColumns(5);
        $grid->column('date', __('Date'));
        $grid->column('spend_type', __('消费类型'))->using(FormData::$FormTypeList)->label();
        $grid->column('spend_name', __('Spend name'));

        $grid->column('project_info', __('Project'))->display(function () {
            $project = $this->projects->first();
            return $project ? $project->title : '其他';
        })->label();
        $grid->column('account.name', __('账户名称'))->label();
        $grid->column('spend', __('Spend'));
        $grid->column('off_spend', __('Off spend'));
        $grid->column('show', __('Show'));
        $grid->column('click', __('Click'));
        $grid->column('department_info', __('科室'))->display(function () {
            return $this->department ? $this->department->title : '-';
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
        $show = new Show(SpendData::findOrFail($id));

        $show->field('date', __('时间'));
        $show->field('type', __('医院类型'))->using(CrmGrabLog::$typeList);
        $show->field('spend_type', __('所属渠道'))->using(FormData::$FormTypeList);
        $show->divider();

        $show->field('spend', __('消耗'));
        $show->field('rebate', __('返点'))->as(function () {
            $val = $this->off_spend;
            return $val ? round($this->spend / $val, 2) : '无返点';
        });
        $show->field('off_spend', __('实消'));

        $show->field('show', __('Show'));
        $show->field('click', __('Click'));

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
     *
     * @param $id
     * @return Form
     */
    protected function form($id = null)
    {
        $form               = new Form(new SpendData);
        $departmentTypeList = DepartmentType::all()->pluck('title', 'id');
        $accountQuery       = AccountData::query()
            ->select(['channel_id', 'name', 'id']);
        $defaultRebate      = 1;

        if ($id && $model = SpendData::find($id)) {
            $accountQuery->whereHas('channel', function ($query) use ($model) {
                $query->where('form_type', $model['spend_type']);
            });

            if ($model['off_spend']) {
                $defaultRebate = round($model['spend'] / $model['off_spend'], 2);
            }

        }

        $form->ignore(['spend_field']);
        $form->display('date', '时间');
        $form->select('type', __('Type'))
            ->options(CrmGrabLog::$typeList)->readOnly();
        $form->select('spend_type', '消费类型')
            ->options(FormData::$FormTypeList)->readOnly();
        $form->display('spend', '消耗');
        $form->hidden('spend', '消耗');
        $form->hidden('off_spend');

        $form->divider();
        $form->currency('spend_field', __('实消返点'))
            ->default($defaultRebate);
        $form->text('spend_name', __('消费名称'));

        $form->projectSelectOfDepartment('department_id', __('所属科室'))
            ->options($departmentTypeList)
            ->load($id, 'projects', 'id', 'title');

        $form->multipleSelect('projects', __('Project'));

        $form->select('account_id', __('账户'))->options($accountQuery->get()->pluck('name', 'id'));


        $form->submitted(function (Form $form) {
            $rebate = (float)request()->get('spend_field');

            if ($rebate < 1 || $rebate > 10) {
                $error = new MessageBag([
                    'title'   => '参数错误',
                    'message' => '返点数据错误',
                ]);

                return back()->with(compact('error'));
            }

            $spend = request()->get('spend');

            $form->off_spend = round($spend / $rebate, 3);

        });

        return $form;
    }
}
