<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Weibo\WeiboGrab;
use App\Models\Channel;
use App\models\CrmGrabLog;
use App\Models\WeiboAccounts;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class WeiboAccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '微博账户管理';

    public function index(Content $content)
    {
        $this->initVue();

        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body("<model-generate-weibo-qr-code/>")
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeiboAccounts);
        $this->appendDataType($grid);
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableFilter();


        $grid->tools(function (Grid\Tools $tools) {
//            $tools->append(new WeiboUpload());
            $tools->append(new WeiboGrab());
        });


        $grid->column('type', "所属")->using([
            'zx' => '整形',
            'kq' => '口腔',
        ])->label();

        $grid->column('name', "名称")->display(function ($val) {
            return $this->username . " (" . $val . ")";
        });
        $grid->column('active', "抓取开关")->switch();
        $grid->column('all_day', "抓取时间")->display(function ($val) {
            return $val
                ? "全天"
                : $this->begin_time . " - " . $this->end_time;
        })->label();
        $grid->column('login_status', "登录状态")->display(function () {
            $data = $this->toJson();
            return "<button-qr-code-login :data='$data'></button-qr-code-login>";
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
        $show = new Show(WeiboAccounts::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('username', __('Username'));
        $show->field('password', __('Password'));
        $show->field('customer_id', __('Customer id'));
        $show->field('active', __('Active'));
        $show->field('all_day', __('All day'));
        $show->field('begin_time', __('Begin time'));
        $show->field('end_time', __('End time'));
        $show->field('type', __('Type'));
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
        $form = new Form(new WeiboAccounts);
        $form->select('type', "所属")
            ->options(CrmGrabLog::$typeList)
            ->required();
        $form->switch('active', "抓取激活");
        $form->text('name', "名称")->required();
        $form->text('username', "账户")->required();
        $form->password('password', "账户密码")->required();
        $form->text('customer_id', "客户ID")->required();
        $form->switch('all_day', "全天抓取");
        $form->switch('enable_cpl', "CPL表单抓取");
        $form->switch('enable_lingdong', "灵动表单抓取");
        $form->timeRange('begin_time', 'end_time', "抓取时段")
            ->default(['start' => '09:00:00', 'end' => '22:00:00']);

        $channelOptions = Channel::all()->pluck('title', 'id');
        $form->select('channel_id', '渠道')->options($channelOptions)->required();

        return $form;
    }
}
