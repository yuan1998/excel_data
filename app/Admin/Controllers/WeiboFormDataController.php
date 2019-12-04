<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Admin\Actions\Weibo\BatchDispatch;
use App\Admin\Actions\WeiboConfigAction;
use App\Admin\Actions\WeiboGrab;
use App\Admin\Actions\WeiboUpload;
use App\Admin\Extensions\Exporter\WeiboFormDataExporter;
use App\Models\FormData;
use App\Models\WeiboFormData;
use App\Models\WeiboUser;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class WeiboFormDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '微博表单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeiboFormData);
        $grid->model()->with(['weiboUser'])->withCount(['recallLog'])->orderBy('upload_date', 'desc')->orderBy('weibo_user_id');
        $this->initVue();

        $grid->header(function ($query) {
            $today              = Carbon::today();
            $todayUnRecallCount = $query->whereDate('upload_date', $today->toDateString())
                ->whereNotNull('weibo_user_id')
                ->whereNull('recall_date')
                ->count();
            $allUnRecallCount   = $query->whereNotNull('weibo_user_id')
                ->whereNull('recall_date')
                ->count();
            $todayUnDispatch    = $query->whereDate('upload_date', $today->toDateString())
                ->whereNull('weibo_user_id')
                ->count();
            $allUnDispatch      = $query->whereNull('weibo_user_id')
                ->count();
            $todayCount         = $query->whereDate('upload_date', $today->toDateString())->count();

            return view('admin.headers.WeiboFormDataHeader', [
                'todayUnRecallCount'   => $todayUnRecallCount,
                'allUnRecallCount'     => $allUnRecallCount,
                'todayUnDispatchCount' => $todayUnDispatch,
                'allUnDispatchCount'   => $allUnDispatch,
                'todayCount'           => $todayCount,
            ]);
        });

        // day name
        $grid->filter(function (Grid\Filter $filter) {
            $filter->column(6, function (Grid\Filter $filter) {
                $filter->like('phone', '电话');
                $options = WeiboUser::all()->pluck('username', 'id');

                $filter->equal('weibo_user_id', '所属人')
                    ->select($options);

                $filter->where(function ($query) {
                    $input = $this->input;
                    if ($input != null) {
                        if ($input == 0) {
                            $query->whereNull('tags');
                        } else {
                            $query->where('tags', $input);
                        }
                    }
                }, '标签')->select(WeiboFormData::$TagList);
            });
            $filter->column(6, function (Grid\Filter $filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    if ($input) {
                        if ($input == 1) {
                            $query->whereNull('weibo_user_id');
                        } elseif ($input == 2) {
                            $query->whereNull('recall_date');
                        } else {
                            $query->whereNotNull('recall_date');
                        }
                    }
                }, '状态')->select([
                    1 => '未分配',
                    2 => '未回访',
                    3 => '已回访',
                ]);
                $filter->betweenDate('real_post_date', '表单日期')
                    ->date();

                $filter->betweenDate('upload_date', '上传日期')
                    ->date();
            });
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->expand();
        });
        $grid->exporter(new WeiboFormDataExporter());

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
//            $tools->append(new WeiboUpload());
            $tools->append(new WeiboGrab());
        });

        $type = $this->appendDataType($grid);

        $grid->disableCreateButton();

        $grid->batchActions(function ($batch) {
            $batch->add(new BatchDispatch());
        });
        $data = WeiboUser::all()->pluck('username', 'id');
        $grid->column('weibo_user_id', '所属')->editable('select', $data);
        $grid->column('recall_date', '状态')->display(function ($val) {
            return $this->weibo_user_id ? ($val ? '已回访' : '未回访') : '未分配';
        })->label();

        $grid->column('tags', '标签')
            ->display(function ($val) {
                return $val ?? 0;
            })
            ->using(WeiboFormData::$TagList)
            ->label();
        $grid->column('recall_log_count', '回访次数');

        $grid->column('project_name', __('项目名称'));
        $grid->column('phone', __('Phone'));
        $grid->column('is_back', '反应时间')->display(function () {
            return $this->recall_date ? Carbon::parse($this->dispatch_date)->diffForHumans($this->recall_date) : '-';
        });
        $grid->column('comment', '回访记录');
        $grid->column('real_post_date', __('表单日期'));
        $grid->column('upload_date', __('上传时间'));
        if (!$type) {
            $grid->column('type', __('类型'))
                ->using([
                    'zx' => '整形',
                    'kq' => '口腔',
                ])->label();
        }

        return $grid;
    }


    public function show($id, Content $content)
    {
        $model = WeiboFormData::query()
            ->with([
                'recallLog' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'recallLog.changeBy'
            ])
            ->find($id);

        $this->initVue();
        return $content
            ->title($this->title())
            ->description($this->description['show'] ?? trans('admin.show'))
            ->row($this->detail($model))
            ->row(view('admin.show.weiboFormDataRecallLog', [
                'recallLog' => $model->recallLog->toArray()
            ]));
    }

    /**
     * Make a show builder.
     *
     * @param mixed $model
     * @return Show
     */
    protected function detail($model)
    {
        $show = new Show($model);

        $show->field('project_name', __('Project name'));
        $show->field('post_date', __('Post date'));
        $show->field('dispatch_date', __('分配时间'));
        $show->field('recall_date', __('回访时间'));
        $show->field('name', __('Name'));
        $show->field('phone', __('Phone'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form    = new Form(new WeiboFormData);
        $options = WeiboUser::all()->pluck('username', 'id');

        $form->select('tags', '标签')->options(WeiboFormData::$TagList);
        $form->select('weibo_user_id', __('Weibo user id'))->options($options);
        $form->saving(function (Form $form) {
            if ($form->tags == 0) {
                $form->tags = null;
            }

        });

        return $form;
    }
}
