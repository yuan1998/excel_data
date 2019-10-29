<?php

namespace App\Console;

use App\Jobs\ArrivingDataYesterday;
use App\Jobs\BillAccountDataYesterday;
use App\Models\ArrivingData;
use App\Models\BillAccountData;
use App\Models\TempCustomerData;
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
        $schedule->call(function () {
            BillAccountData::todayBillAccountData('zx');
        })->dailyAt('22:00');
        $schedule->call(function () {
            BillAccountData::todayBillAccountData('kq');
        })->dailyAt('22:20');

        $schedule->call(function () {
            ArrivingData::getToday('zx');
        })->dailyAt('22:40');
        $schedule->call(function () {
            ArrivingData::getToday('kq');
        })->dailyAt('22:50');


        $schedule->call(function () {
            TempCustomerData::getToday('zx');
        })->dailyAt('22:40');
        $schedule->call(function () {
            TempCustomerData::getToday('kq');
        })->dailyAt('22:50');

        $schedule->call(function () {
            TempCustomerData::yesterday('zx');
        })->dailyAt('22:40');
        $schedule->call(function () {
            TempCustomerData::yesterday('kq');
        })->dailyAt('22:50');

        $schedule->call(function () {
            BillAccountData::yesterdayBillAccountData('zx');
        })->dailyAt('23:00');
        $schedule->call(function () {
            BillAccountData::yesterdayBillAccountData('kq');
        })->dailyAt('23:20');


        $schedule->call(function () {
            ArrivingData::getYesterday('zx');
        })->dailyAt('23:00');
        $schedule->call(function () {
            ArrivingData::getYesterday('kq');
        })->dailyAt('23:20');

        $schedule->call(function () {
            ArrivingData::getCurrentMonth('zx');
        })->monthlyOn(date('t'), '23:30');
        $schedule->call(function () {
            ArrivingData::getCurrentMonth('kq');
        })->monthlyOn(date('t'), '23:35');

        $schedule->call(function () {
            BillAccountData::monthBillAccountData('zx');
        })->monthlyOn(date('t'), '23:40');
        $schedule->call(function () {
            BillAccountData::monthBillAccountData('kq');
        })->monthlyOn(date('t'), '23:45');

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
