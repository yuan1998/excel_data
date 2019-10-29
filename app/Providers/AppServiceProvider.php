<?php

namespace App\Providers;

use App\Jobs\CustomerPhoneCheckJob;
use App\Jobs\ExportJob;
use App\Models\BaiduClue;
use App\models\CrmGrabLog;
use App\Models\CustomerPhone;
use App\Models\ExportDataLog;
use App\Models\FeiyuData;
use App\Models\FormDataPhone;
use App\Models\WeiboData;
use App\Models\WeiboFormData;
use App\Models\WeiboUser;
use App\Observers\BaiduClueObserver;
use App\Observers\CrmGrabLogObserver;
use App\Observers\CustomerPhoneObserver;
use App\Observers\ExportDataLogObserver;
use App\Observers\FeiyuObserver;
use App\Observers\FormDataPhoneObserver;
use App\Observers\WeiboDataObserver;
use App\Observers\WeiboFormDataObserver;
use App\Observers\WeiboUserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        ini_set("max_execution_time", "3000");

        FormDataPhone::observe(FormDataPhoneObserver::class);
        CrmGrabLog::observe(CrmGrabLogObserver::class);
        ExportDataLog::observe(ExportDataLogObserver::class);
        CustomerPhone::observe(CustomerPhoneObserver::class);

        WeiboUser::observe(WeiboUserObserver::class);
        WeiboFormData::observe(WeiboFormDataObserver::class);
    }
}
