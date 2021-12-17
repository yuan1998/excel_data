<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Admin\Actions\FormData\BatchRecheckItem;
use App\Admin\Actions\FormData\BatchRecheckPhones;
use App\Admin\Actions\FormData\RecheckItem;
use App\Admin\Actions\FormData\RecheckPhones;
use App\Admin\Actions\RecheckFormAction;
use App\Admin\Extensions\Exporter\FormDataExporter;
use App\Models\AccountData;
use App\Models\Channel;
use App\models\CrmGrabLog;
use App\Models\DepartmentType;
use App\Models\FormData;
use App\Models\FormDataPhone;
use App\Models\ProjectType;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class FormDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '表单数据管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        static::initVue();
        static::clearAutoComplete();

        $grid = new Grid(new FormData);
        $grid->model()
            ->with(['phones', 'projects', 'department', 'account', 'channel'])
            ->orderBy('date', 'desc');

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

                    switch ($val) {
                        case 0 :
                        case 1 :
                        case 2 :
                            $query->whereHas('phones', function ($query) use ($val) {
                                $query->where('is_archive', $val);
                            });
                            break;
                        case 3:
                            $query->whereHas('phones', function ($query) use ($val) {
                                $query->where('is_repeat', 2);
                            });
                            break;
                        case 4:
                            $query->whereHas('phones', function ($query) use ($val) {
                                $query->where('is_repeat', '<>', 2)
                                    ->whereIn('intention', [0, 1])
                                    ->where('is_archive', 1);
                            });
                            break;
                        case 5:
                            $query->whereHas('phones', function ($query) {
                                $query->where('is_repeat', '<>', 2)
                                    ->where('medium_error', 1);
                            });
                            break;
                        case 6:
                            $query->whereHas('phones', function ($query) {
                                $query->where('is_repeat', '<>', 2)
                                    ->where('medium_error', 0);
                            });
                            break;
                    }
                }, '建档状态')->select([
                    0 => '未查询',
                    1 => '已建档',
                    2 => '未建档',
                    3 => '重复建档',
                    4 => '未下单',
                    5 => '媒介不一致',
                    6 => '媒介未查询',
                ]);

                $filter->between('date', '日期')->date();
            });

            $filter->column(6, function (Grid\Filter $filter) {
                $filter->equal('type', '数据类型')->select(CrmGrabLog::$typeList);

                $filter->equal('channel_id', '所属渠道')->select(Channel::query()->pluck('title', 'id'));

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
//        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->exporter(new FormDataExporter());

        $grid->batchActions(function ($batch) {
            $batch->add(new BatchRecheckItem());
            $batch->add(new BatchRecheckPhones());
        });

        $grid->actions(function ($actions) {
            $actions->add(new RecheckItem());
            $actions->add(new RecheckPhones());
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new RecheckFormAction());
        });

        $grid->column('department_info', __('科室'))->display(function () {
            return $this->department ? $this->department->title : '-';
        });
        $grid->column('channel.title', __('所属渠道'));
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
     * @param null $id
     * @return Form
     */
    protected function form($id = null)
    {
        $form = new Form(new FormData);

        $departmentTypeList = DepartmentType::all()->pluck('title', 'id')->toArray();
        $accountList        = AccountData::all()->pluck('name', 'id')->toArray();

        $form->text('date', '时间')->disable();
        $form->select('type', __('Type'))->options(CrmGrabLog::$typeList);

        $form->divider('关联数据');
        $form->select('form_type', '表单类型')->options(FormData::$FormTypeList);
        $form->select('account_id', __('所属账户'))
            ->options($accountList);
        $form->projectSelectOfDepartment('department_id', __('所属科室'))
            ->options($departmentTypeList)
            ->load($id, 'projects', 'id', 'title');

        $form->multipleSelect('projects', __('Project'));
        $form->text('data_type', '关键分配词');
        $form->divider('关联手机号码');
        $form->hasMany('phones', '手机号码', function (Form\NestedForm $form) {
            $form->text('phone', '手机号码');
            $form->select('is_archive', '建档状态')
                ->options(FormDataPhone::$IsArchiveList);

            $form->select('is_repeat', '是否重复')
                ->options(FormDataPhone::$IsRepeatList);
            $form->select('intention', '建档等级')
                ->options(FormDataPhone::$IntentionList);

            $form->disableSubmit();

        });


        return $form;
    }
}
