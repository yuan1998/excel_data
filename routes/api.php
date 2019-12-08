<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * @var Dingo\Api\Routing\Router
 */
$api = app('Dingo\Api\Routing\Router');


$api->version('v1', [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => ['bindings']
], function ($api) {
    /**
     * BaiduData:百度数据 Api
     */
    $api->get('/baidu', "BaiduDataController@index")
        ->name('api.baidu.index');

    $api->put('/baidu/{baiduData}', "BaiduDataController@update")
        ->name('api.baidu.update');
    $api->post('/baidu', "BaiduDataController@store")
        ->name('api.baidu.store');
    $api->post('/saveRequest', "BaiduDataController@saveRequest")
        ->name('api.baidu.saveRequest');

    $api->delete('/baidu/{ids}', "BaiduDataController@destroy")
        ->name('api.baidu.destroy');
    $api->post('/baidu/upload', "BaiduDataController@uploadExcel")
        ->name('api.baidu.uploadExcel');

    $api->post('/baidu/test', "BaiduDataController@test")
        ->name('api.baidu.test');

    /**
     *  BaiduClue:百度线索 Api
     */
    $api->post('/baiduClue/checkArchive/{baiduClue}', "BaiduClueController@checkItemArchive")
        ->name('api.baiduClue.checkItemArchive');
    $api->post('/baiduClue/checkIntention/{baiduClue}', "BaiduClueController@checkItemIntention")
        ->name('api.baiduClue.checkItemIntention');
    $api->post('/baiduClue/checkItem/{baiduClue}', "BaiduClueController@checkItem")
        ->name('api.baiduClue.checkItem');
    $api->post('/baiduClue/checkArriving/{baiduClue}', "BaiduClueController@checkItemArriving")
        ->name('api.baiduClue.checkItemArriving');


    /**
     * WeiboData:微博数据 Api
     */
    $api->post('/weibo/upload', "WeiboController@uploadExcel")
        ->name('api.weibo.uploadExcel');

    /**
     * FeiyuData:飞鱼数据 Api
     */
    $api->post('/feiyu/upload', "FeiyuController@uploadExcel")
        ->name('api.feiyu.uploadExcel');
    $api->post('/feiyu/checkArchive/{feiyuData}', "FeiyuController@checkItemArchive")
        ->name('api.feiyu.checkItemArchive');
    $api->post('/feiyu/checkIntention/{feiyuData}', "FeiyuController@checkItemIntention")
        ->name('api.baiduClue.checkItemIntention');
    $api->post('/feiyu/checkItem/{feiyuData}', "FeiyuController@checkItem")
        ->name('api.baiduClue.checkItem');

    /**
     * BaiduSpend: 百度消费 Api
     */
    $api->post('/baiduSpend/upload', "BaiduSpendController@uploadExcel")
        ->name('api.baiduSpend.uploadExcel');
    $api->post('/feiyuSpend/upload', "FeiyuSpendController@uploadExcel")
        ->name('api.feiyuSpend.uploadExcel');
    $api->post('/weiboSpend/upload', "WeiboSpendController@uploadExcel")
        ->name('api.weiboSpend.uploadExcel');
    $api->get('/department/archives', "DepartmentController@departmentArchives")
        ->name('api.department.archives');


    $api->group([
        'prefix' => 'import',
    ], function ($api) {
        $api->post('/formExcel', "ImportExcelController@uploadFormDataExcel")
            ->name('api.import.formExcel');
        $api->post('/auto', "ImportExcelController@uploadAutoExcel")
            ->name('api.import.auto');

        $api->post('/excel/make', "ImportExcelController@exportExcelStore")
            ->name('api.import.excelMake');

    });
    $api->group([
        'prefix' => 'export',
    ], function ($api) {
        $api->post('/excel', "ImportExcelController@exportExcelStore")
            ->name('api.import.excelMake');

    });


    $api->group([
        'prefix' => 'weibo',
    ], function ($api) {
        $api->post('/authenticate', 'WeiboAuthController@authenticate')
            ->name('api.weibo.authenticate');

        $api->get('/authenticate', 'WeiboAuthController@current')
            ->name('api.weibo.current');
        $api->put('/authenticate/current', 'WeiboAuthController@refresh')
            ->name('api.weibo.authenticate.refresh');
        $api->delete('/authenticate/current', 'WeiboAuthController@destroyToken')
            ->name('api.weibo.authenticate.destroyToken');

        $api->put('/user/pause', 'WeiboUserController@userPause')
            ->name('api.weibo.user.userPause');


        $api->get('/formData/', 'WeiboFormDataController@userIndex')
            ->name('api.weiboFormData.index');
        $api->get('/formData/hasNew', 'WeiboFormDataController@userHasNew')
            ->name('api.weiboFormData.userHasNew');
        $api->put('/formData/{formData}', 'WeiboFormDataController@userUpdate')
            ->name('api.weiboFormData.update');
        $api->post('/grabFormData/', 'WeiboFormDataController@grabWeiboFormData')
            ->name('api.weiboFormData.grabWeiboFormData');


        $api->get('/user/pause', "WeiboUserController@updatePause")
            ->name('api.weiboUser.updatePause');

        $api->post('/setting', "WeiboDispatchSettingController@ruleStore")
            ->name('api.WeiboDispatchSetting.ruleStore');
        $api->post('/setting/base', "WeiboDispatchSettingController@saveSetting")
            ->name('api.WeiboDispatchSetting.saveSetting');
        $api->put('/setting/{dispatchSetting}', "WeiboDispatchSettingController@ruleUpdate")
            ->name('api.WeiboDispatchSetting.ruleUpdate');
        $api->delete('/setting/{ids}', "WeiboDispatchSettingController@destroy")
            ->name('api.WeiboDispatchSetting.destroy');


    });


});
