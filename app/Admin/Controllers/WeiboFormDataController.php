<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Admin\Actions\Weibo\BatchDispatch;
use App\Admin\Actions\WeiboUpload;
use App\Models\WeiboFormData;
use App\Models\WeiboUser;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class WeiboFormDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\WeiboFormData';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeiboFormData);
        $grid->model()->orderBy('upload_date', 'desc');

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
                $filter->between('post_date' , '表单日期')
                    ->date();
            });
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->expand();
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
            $tools->append(new WeiboUpload());
        });

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

        $grid->column('phone', __('Phone'));
        $grid->column('is_back', '反应时间')->display(function () {
            return $this->recall_date ? Carbon::parse($this->upload_date)->diffForHumans($this->recall_date) : '-';
        });
        $grid->column('comment', '回访记录');
        $grid->column('post_date', __('表单日期'));
        $grid->column('upload_date', __('上传时间'));

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
        $show = new Show(WeiboFormData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('weibo_id', __('Weibo id'));
        $show->field('project_id', __('Project id'));
        $show->field('project_name', __('Project name'));
        $show->field('post_date', __('Post date'));
        $show->field('name', __('Name'));
        $show->field('phone', __('Phone'));
        $show->field('category_type', __('Category type'));
        $show->field('feedback', __('Feedback'));
        $show->field('comment', __('Comment'));
        $show->field('weixin', __('Weixin'));
        $show->field('remark', __('Remark'));
        $show->field('weibo_user_id', __('Weibo user id'));
        $show->field('update_date', __('Update date'));
        $show->field('recall_date', __('Recall date'));
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
        $form = new Form(new WeiboFormData);

        $form->text('weibo_id', __('Weibo id'));
        $form->text('project_id', __('Project id'));
        $form->text('project_name', __('Project name'));
        $form->text('post_date', __('Post date'));
        $form->text('name', __('Name'));
        $form->mobile('phone', __('Phone'));
        $form->text('category_type', __('Category type'));
        $form->text('feedback', __('Feedback'));
        $form->text('comment', __('Comment'));
        $form->text('weixin', __('Weixin'));
        $form->text('remark', __('Remark'));
        $form->number('weibo_user_id', __('Weibo user id'));
        $form->datetime('update_date', __('Update date'))->default(date('Y-m-d H:i:s'));
        $form->datetime('recall_date', __('Recall date'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
