<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('/weibo_user/settings', 'WeiboUserController@settings')->name('admin.weiboUser.settings');

    $router->resource('baidu_data', 'BaiduDataController');
    $router->resource('weibo_data', 'WeiboDataController');
    $router->resource('feiyu_data', "FeiyuDataController");
    $router->resource('project_type', "ProjectTypeController");
    $router->resource('department_type', "DepartmentTypeController");
    $router->resource('arriving_data', "ArrivingDataController");
    $router->resource('baidu_spend', 'BaiduSpendController');
    $router->resource('feiyu_spend', 'FeiyuSpendController');
    $router->resource('weibo_spend', "WeiboSpendController");
    $router->resource('medium_type', "MediumTypeController");
    $router->resource('form_data', 'FormDataController');
    $router->resource('spend_data', 'SpendDataController');
    $router->resource('channels', "ChannelController");
    $router->resource('bill_account_data', "BillAccountDataController");
    $router->resource('crm_grab_logs', "CrmGrabLogController");
    $router->resource('export_data_logs', "ExportDataLogController");
    $router->resource('account_data', "AccountDataController");
    $router->resource('weibo_user', 'WeiboUserController');
    $router->resource('weibo_form_data', "WeiboFormDataController");
});
