<?php

namespace App\Admin\Controllers;

use App\Models\ArrivingData;
use App\Models\BaiduData;
use App\Models\Channel;
use App\Models\DepartmentType;
use App\Models\FeiyuData;
use App\Models\WeiboData;
use App\Models\WeiboFormData;
use App\Parsers\ParserArrivingData;
use Carbon\Carbon;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\DB;

class HomeController extends AdminController
{

    protected $title = '业务总览';

    protected $description = '';

    public function index(Content $content)
    {
        $this->initVue();

        return $content
            ->title($this->title)
            ->description($this->description)
            ->row(function (Row $row) {

                if (Admin::user()->isRole('weibo_admin') || Admin::user()->isAdministrator()) {
                    $now  = Carbon::now();
                    $old  = Carbon::parse('-1 months');
                    $data = WeiboFormData::query()
                        ->whereBetween('post_date', [$old, $now])
                        ->get();

                    $row->column(12, "<weibo-index :weibo-form-data='$data'></weibo-index>");
                }

            });
    }

    public function weiboIndex()
    {

    }

}
