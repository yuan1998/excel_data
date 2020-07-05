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


    $router->group([
        'prefix' => 'media-manager',
    ], function (Router $router) {
        $router->get('/', 'MediaController@index')
            ->name('vue-media-index');
        $router->get('download', 'MediaController@download')
            ->name('vue-media-download');
        $router->delete('delete', 'MediaController@delete')
            ->name('vue-media-delete');
        $router->put('move', 'MediaController@move')
            ->name('vue-media-move');
        $router->post('upload', 'MediaController@upload')
            ->name('vue-media-upload');
        $router->post('folder', 'MediaController@newFolder')
            ->name('vue-media-new-folder');

    });

    $router->group([
        'prefix' => 'sanfang_export_data_logs',
    ], function ($router) {
        $router->get('/', 'ExportDataLogController@sanfangIndex')
            ->name('admin.sanfang.export');
    });


    $router->resource('consultants', ConsultantController::class);
    $router->resource('consultant-groups', ConsultantGroupController::class);
    $router->resource('form-type-lists', FormTypeListController::class);
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
    $router->resource('weibo_accounts', "WeiboAccountController");
});
