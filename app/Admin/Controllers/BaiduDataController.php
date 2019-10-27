<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ExcelUpload;
use App\Clients\BaseClient;
use App\Models\BaiduData;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class BaiduDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\BaiduData';


    /**
     * Index interface.
     *
     * @param Content     $content
     * @param string|null $type
     * @return Content
     */
    public function index(Content $content, $type = null)
    {
        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($this->grid($type));
    }


    /**
     * Make a grid builder.
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BaiduData);

        $type = $this->appendDataType($grid);

        $grid->tools(function (Grid\Tools $tools) use ($type) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append(new ExcelUpload($type ?? 'zx', 'baidu'));
        });
        $grid->disableActions();
        $grid->disableCreateButton();


        $grid->column('visitor_name', __('访客姓名'))->expand(function (BaiduData $model) {
            $clues = $model->clues()->get()->map(function ($item) {
                $item->intention     = BaseClient::$intention_list[$item->intention];
                $item->is_archive    = $item->is_archive ? '是' : '否';
                $item->has_dialog_id = $item->has_dialog_id ? '是' : '否';
                $item->has_url       = $item->has_url ? '是' : '否';

                return $item->only(['phone', 'is_archive', 'intention', 'has_dialog_id', 'has_url']);
            });
            return new Table(['电话', '已建档', '意向度', '是否有会话ID', '是否有url'], $clues->toArray());
        });

        $grid->column('cur_access_time', '访问日期');
        $grid->column('visitor_type', '访客类型');
        $grid->column('first_url', '追踪码')->display(function ($value) {
            preg_match("/\?A(.*?)\//", $value, $match);
            return isset($match[0]) ? $match[0] : '--';
        });

        $grid->column('visitor_id', __('访客 id'))->copyable();

//        $grid->column('type', __('Type'));

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
        $show = new Show(BaiduData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('cur_access_time', __('Cur access time'));
        $show->field('city', __('City'));
        $show->field('dialog_type', __('Dialog type'));
        $show->field('visitor_name', __('Visitor name'));
        $show->field('visitor_type', __('Visitor type'));
        $show->field('first_customer', __('First customer'));
        $show->field('first_url', __('First url'));
        $show->field('keyword', __('Keyword'));
        $show->field('ip', __('Ip'));
        $show->field('url', __('Url'));
        $show->field('dialog_id', __('Dialog id'));
        $show->field('visitor_id', __('Visitor id'));
        $show->field('first_access_date', __('First access date'));
        $show->field('previous_access_date', __('Previous access date'));
        $show->field('start_dialog_date', __('Start dialog date'));
        $show->field('all_keyword', __('All keyword'));
        $show->field('search_engine', __('Search engine'));
        $show->field('dialog_url', __('Dialog url'));
        $show->field('dialog_keyword', __('Dialog keyword'));
        $show->field('bidding_keyword', __('Bidding keyword'));
        $show->field('site', __('Site'));
        $show->field('clue', __('Clue'));
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
        $form = new Form(new BaiduData);

        $form->text('cur_access_time', __('Cur access time'));
        $form->text('city', __('City'));
        $form->text('dialog_type', __('Dialog type'));
        $form->text('visitor_name', __('Visitor name'));
        $form->text('visitor_type', __('Visitor type'));
        $form->text('first_customer', __('First customer'));
        $form->text('first_url', __('First url'));
        $form->text('keyword', __('Keyword'));
        $form->ip('ip', __('Ip'));
        $form->url('url', __('Url'));
        $form->text('dialog_id', __('Dialog id'));
        $form->text('visitor_id', __('Visitor id'));
        $form->text('first_access_date', __('First access date'));
        $form->text('previous_access_date', __('Previous access date'));
        $form->text('start_dialog_date', __('Start dialog date'));
        $form->text('all_keyword', __('All keyword'));
        $form->text('search_engine', __('Search engine'));
        $form->text('dialog_url', __('Dialog url'));
        $form->text('dialog_keyword', __('Dialog keyword'));
        $form->text('bidding_keyword', __('Bidding keyword'));
        $form->text('site', __('Site'));
        $form->text('clue', __('Clue'));
        $form->text('type', __('Type'))->default('zx');

        return $form;
    }
}
