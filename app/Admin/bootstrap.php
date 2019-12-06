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

use App\Admin\Extentions\Fields\MySelect;
use App\Admin\Filters\BetweenDate;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid\Filter;

Admin::headerJs('//cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.min.js');
Admin::js('https://cdn.jsdelivr.net/npm/axios@0.19.0/dist/axios.min.js');
Admin::js('https://cdn.jsdelivr.net/npm/echarts@4.5.0/dist/echarts.min.js');
Admin::js('/js/app.js');
Admin::css('//cdn.jsdelivr.net/npm/element-ui@2.12.0/lib/theme-chalk/index.css');
Admin::css('/css/app.css');
// Admin::css('https://cdn.jsdelivr.net/npm/v-charts/lib/style.min.css');
app('view')->prependNamespace('admin', resource_path('views/admin/views'));

Form::extend('mySelect', MySelect::class);
Filter::extend('betweenDate' , BetweenDate::class);
Form::forget(['map', 'editor']);
