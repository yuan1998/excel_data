<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});
Route::group([
    'prefix' => 'weibo'
], function () {
    Route::get('/login', "WeiboUserController@loginPage");
});


Route::get('/excelView/{key}', "ExcelViewController@index");


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
