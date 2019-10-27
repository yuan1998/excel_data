<?php

namespace App\Admin\Controllers;

use App\Models\ArrivingData;
use App\Models\BaiduData;
use App\Models\Channel;
use App\Models\DepartmentType;
use App\Models\FeiyuData;
use App\Models\WeiboData;
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
    public function index(Content $content)
    {


        $queues = DB::table('jobs')
            ->select(DB::raw('COUNT(queue) as count'), 'queue')
            ->whereIn('queue', ['baidu', 'weibo', 'feiyu'])
            ->groupBy('queue')
            ->get();

        $types = DepartmentType::with('projects')->get();
        $channels = Channel::all();

        $this->initVue();

        return $content
            ->title('Dashboard')
            ->description('Description...')
            ->row(function(Row $row) {
                $row->column(4, '<example-component></example-component>');
            });
    }
}
