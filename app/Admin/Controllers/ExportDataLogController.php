<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\CrmGrabData;
use App\Admin\Actions\ExportDataAction;
use App\Admin\Actions\SanfangExportAction;
use App\Models\ExportDataLog;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Tools\BatchActions;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportDataLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '导出表格';


    protected function sanfangIndex(Content $content)
    {
        return $content
            ->title($this->title() . ' - 三方')
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($this->sanfangGrid());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function sanfangGrid()
    {
        $grid = new Grid(new ExportDataLog);

        $grid->model()
            ->where('data_type', 'sanfang_data_excel')
            ->orderBy('id', 'desc');
        $this->initVue();

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (BatchActions $batch) {
                $batch->disableDelete();
            });

            $tools->append(new SanfangExportAction());
        });

        $grid->actions(function ($actions) {
            // 去掉编辑
            $actions->disableEdit();

            // 去掉查看
            $actions->disableView();
        });

        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->header(function () {
            $queueName = [
                'form_data_phone'    => '表单电话',
                'crm_grab_log_queue' => 'crm 数据',
                'data_exports'       => '导出数据',
            ];

            $data = DB::table('jobs')
                ->select([
                    DB::raw('count(queue) as count'),
                    'queue'
                ])->whereIn('queue', array_keys($queueName))->groupBy('queue')->get()->pluck('count', 'queue');


            return view('admin.headers.ExportDataLogHeader', [
                'queueName' => $queueName,
                'data'      => $data,
            ]);
        });

        $grid->column('created_at', __('Created at'));
        $grid->column('full_path', '文件名称')->display(function () {
            return Storage::disk('public')->url($this->path . $this->file_name);
        })->downloadable();
        $grid->column('run_time', __('运行时间'))->display(function ($value) {
            return $value;
        });
        $grid->column('status', __('Status'))->using(ExportDataLog::$staticList)->label();

        return $grid;
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ExportDataLog);

        $grid->model()
            ->whereIn('data_type', ['xxl_data_excel', 'baidu_plan','consultant_group_excel'])
            ->orderBy('id', 'desc');
        $this->initVue();

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (BatchActions $batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExportDataAction());
        });

        $grid->actions(function ($actions) {
            // 去掉编辑
            $actions->disableEdit();

            // 去掉查看
            $actions->disableView();
        });

        $grid->disableCreateButton();

        $grid->header(function () {
            $queueName = [
                'form_data_phone'    => '表单电话',
                'crm_grab_log_queue' => 'crm 数据',
                'data_exports'       => '导出数据',
            ];

            $data = DB::table('jobs')
                ->select([
                    DB::raw('count(queue) as count'),
                    'queue'
                ])->whereIn('queue', array_keys($queueName))->groupBy('queue')->get()->pluck('count', 'queue');


            return view('admin.headers.ExportDataLogHeader', [
                'queueName' => $queueName,
                'data'      => $data,
            ]);
        });

        $grid->column('created_at', __('Created at'));
        $grid->column('full_path', '文件名称')->display(function () {
            return Storage::disk('public')->url($this->path . $this->file_name);
        })->downloadable();
        $grid->column('run_time', __('运行时间'))->display(function ($value) {
            return $value;
        });
        $grid->column('status', __('Status'))->using(ExportDataLog::$staticList)->label();

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
        $show = new Show(ExportDataLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('file_name', __('File name'));
        $show->field('path', __('Path'));
        $show->field('status', __('Status'));
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
        $form = new Form(new ExportDataLog);

        $form->text('file_name', __('File name'));
        $form->text('path', __('Path'));
        $form->number('status', __('Status'));

        return $form;
    }
}
