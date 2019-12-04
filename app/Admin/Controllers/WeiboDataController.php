<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Clients\BaseClient;
use App\Models\WeiboData;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Tools\BatchActions;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class WeiboDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\WeiboData';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeiboData);
        $type = $type = $this->appendDataType($grid);

        $grid->tools(function (Grid\Tools $tools) use ($type) {
            $tools->batch(function (BatchActions $batch) {
                $batch->disableDelete();
            });
            $tools->append(new ExcelUpload($type ?? 'zx', 'weibo'));
        });
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->column('is_archive', __('Is archive'))->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->column('intention', __('Intention'))->display(function ($value) {
            return BaseClient::$intention_list[$value];
        });
        $grid->column('project_name', __('Project name'));
        $grid->column('post_date', __('Post date'));
        $grid->column('name', __('Name'));
        $grid->column('phone', __('Phone'));
        $grid->column('category_type', __('Category type'));
        $grid->column('weixin', __('Weixin'));
        
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
        $show = new Show(WeiboData::findOrFail($id));

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
        $show->field('is_archive', __('Is archive'));
        $show->field('intention', __('Intention'));
        $show->field('type', __('Type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('arriving_type', __('Arriving type'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WeiboData);

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
        $form->switch('is_archive', __('Is archive'));
        $form->number('intention', __('Intention'));
        $form->text('type', __('Type'))->default('zx');
        $form->number('arriving_type', __('Arriving type'));

        return $form;
    }
}
