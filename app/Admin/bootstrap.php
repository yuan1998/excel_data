<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use App\Admin\Extensions\Fields\MySelect;
use App\Admin\Extensions\Fields\ProjectSelectOfDepartment;
use App\Admin\Filters\BetweenDate;
use App\Admin\Filters\Test;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid\Filter;

Admin::css('http://unpkg.zhimg.com/element-ui@2.12.0/lib/theme-chalk/index.css');
Admin::headerJs('http://unpkg.zhimg.com/vue@2.6.10/dist/vue.min.js');
Admin::js('http://unpkg.zhimg.com/axios@0.19.0/dist/axios.min.js');
Admin::js('https://cdn.staticfile.org/echarts/4.5.0/echarts.min.js');
Admin::js('https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/xlsx/0.18.2/xlsx.full.min.js');

Admin::js('/js/app.js');
Admin::css('/css/app.css');

app('view')->prependNamespace('admin', resource_path('views/admin/views'));

Filter::extend('betweenDate', BetweenDate::class);
Form::forget(['map', 'editor']);
Form::extend('mySelect', MySelect::class);
Form::extend('projectSelectOfDepartment', ProjectSelectOfDepartment::class);
