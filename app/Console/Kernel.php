<?php

namespace App\Console;

use App\Clients\WeiboClient;
use App\Jobs\ArrivingDataYesterday;
use App\Jobs\BillAccountDataYesterday;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\FormData;
use App\Models\TempCustomerData;
use App\Models\WeiboFormData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
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
        // 拉取 CRM消费数据
        $schedule->call(function () {
            BillAccountData::todayBillAccountData('zx');
        })->dailyAt('22:00');
        $schedule->call(function () {
            BillAccountData::todayBillAccountData('kq');
        })->dailyAt('22:20');

        // 拉取 CRM到院数据
        $schedule->call(function () {
            ArrivingData::getToday('zx');
        })->dailyAt('22:40');
        $schedule->call(function () {
            ArrivingData::getToday('kq');
        })->dailyAt('22:50');

        // 拉取今天的 CRM临客数据
        $schedule->call(function () {
            TempCustomerData::getToday('zx');
        })->dailyAt('22:40');
        $schedule->call(function () {
            TempCustomerData::getToday('kq');
        })->dailyAt('22:50');

        // 拉取昨天的 CRM临客数据
        $schedule->call(function () {
            TempCustomerData::yesterday('zx');
        })->dailyAt('22:40');
        $schedule->call(function () {
            TempCustomerData::yesterday('kq');
        })->dailyAt('22:50');

        // 拉取昨天的 CRM消费数据
        $schedule->call(function () {
            BillAccountData::yesterdayBillAccountData('zx');
        })->dailyAt('23:00');
        $schedule->call(function () {
            BillAccountData::yesterdayBillAccountData('kq');
        })->dailyAt('23:20');

        // 拉取昨天的 CRM到院数据
        $schedule->call(function () {
            ArrivingData::getYesterday('zx');
        })->dailyAt('23:00');
        $schedule->call(function () {
            ArrivingData::getYesterday('kq');
        })->dailyAt('23:20');

        // 在每个月最后一天,重新拉取整个月的 到院数据
        $schedule->call(function () {
            ArrivingData::getCurrentMonth('zx');
        })->monthlyOn(date('t'), '23:30');
        $schedule->call(function () {
            ArrivingData::getCurrentMonth('kq');
        })->monthlyOn(date('t'), '23:35');

        // 在每个月最后一天,重新拉取整个月的 消费数据
        $schedule->call(function () {
            BillAccountData::monthBillAccountData('zx');
        })->monthlyOn(date('t'), '23:40');
        $schedule->call(function () {
            BillAccountData::monthBillAccountData('kq');
        })->monthlyOn(date('t'), '23:45');


        // 每隔15分钟,拉取一次微博表单数据
        $schedule->call(function () {
            foreach (WeiboClient::$Account as $accountName => $value) {
                WeiboFormData::pullToday($accountName);
            }
        })->everyFifteenMinutes();

        // 每天拉取一次昨天的微博表单数据,以防错漏
        $schedule->call(function () {
            foreach (WeiboClient::$Account as $accountName => $value) {
                WeiboFormData::pullYesterday($accountName);
            }
        })->daily();

        // 每个月月底,重新查询一遍表单的建档情况 (针对微博表单)
        $schedule->call(function () {
            FormData::recheckMonthPhoneStatus();
        })->monthlyOn(date('t'), '23:55');


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
