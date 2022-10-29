<?php

namespace App\Console;

use App\Clients\WeiboClient;
use App\Jobs\ArrivingDataYesterday;
use App\Jobs\BillAccountDataYesterday;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\FormData;
use App\Models\FormDataPhone;
use App\Models\TempCustomerData;
use App\Models\WeiboAccounts;
use App\Models\WeiboFormData;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {
            TempCustomerData::clearBeforeData();
            ArrivingData::clearBeforeData();
            BillAccountData::clearBeforeData();
            FormData::clearBeforeData();
            FormDataPhone::clearBeforeData();

        })->daily();


        if (env('PULL_CRM_DATA',false)) {
            // 拉取昨天的 CRM临客数据
            $schedule->call(function () {
                TempCustomerData::yesterday('zx');
            })->dailyAt('03:03');
            $schedule->call(function () {
                TempCustomerData::yesterday('kq');
            })->dailyAt('03:03');

            // 拉取昨天的 CRM消费数据
            $schedule->call(function () {
                BillAccountData::yesterdayBillAccountData('zx');
            })->dailyAt('03:01');
            $schedule->call(function () {
                BillAccountData::yesterdayBillAccountData('kq');
            })->dailyAt('03:01');

            // 拉取昨天的 CRM到院数据
            $schedule->call(function () {
                ArrivingData::getYesterday('zx');
            })->dailyAt('03:02');
            $schedule->call(function () {
                ArrivingData::getYesterday('kq');
            })->dailyAt('03:02');

            // 在每个月最后一天,重新拉取整个月的 到院数据
            $schedule->call(function () {
                ArrivingData::getCurrentMonth('zx');
            })->monthlyOn(date('t'), '03:04');
            $schedule->call(function () {
                ArrivingData::getCurrentMonth('kq');
            })->monthlyOn(date('t'), '03:04');

            // 在每个月最后一天,重新拉取整个月的 消费数据
            $schedule->call(function () {
                BillAccountData::monthBillAccountData('zx');
            })->monthlyOn(date('t'), '03:05');
            $schedule->call(function () {
                BillAccountData::monthBillAccountData('kq');
            })->monthlyOn(date('t'), '03:05');
        }




        // 当天
        // 每隔15分钟,拉取一次微博表单数据
        if (env('PULL_WEIBO_DATA', false)) {
            $schedule->call(function () {
                $today = Carbon::today()->toDateString();
                WeiboAccounts::checkAccountIsRun($today, $today);
            })->everyFifteenMinutes();

            // 隔天
            // 每天拉取一次昨天的微博表单数据,以防错漏
            $schedule->call(function () {
                $yesterday = Carbon::yesterday()->toDateString();
                WeiboAccounts::checkAccountIsRun($yesterday, $yesterday);
            })->daily();
            //
            //        // 每个月月底,重新查询一遍表单的建档情况 (针对微博表单)
            //        $schedule->call(function () {
            //            FormData::recheckMonthPhoneStatus();
            //        })->monthlyOn(date('t'), '23:55');
        }


    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
