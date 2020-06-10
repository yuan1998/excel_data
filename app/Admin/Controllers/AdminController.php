<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\ChannelTypeFilter;
use App\Admin\Extensions\Tools\DataType;
use App\Admin\Extensions\Tools\DepartmentDataType;
use App\Admin\Extensions\Tools\FormTypeFilter;
use App\Models\DepartmentType;
use App\Models\FormData;
use Encore\Admin\Controllers\AdminController as BaseController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;


class AdminController extends BaseController
{

    public static function initVue()
    {
        Admin::script(<<<EOF
        const app = new Vue({
            el: '#app'
        });
EOF
        );
    }

    public static function clearAutoComplete()
    {
        Admin::script(<<<EOF
        $(function () {
        $('input.form-control').attr('autocomplete' , 'off');
        });
EOF
        );
    }

    public function appendDataType(Grid $grid)
    {
        $type = request()->get('type');
        $type = in_array($type, ['zx', 'kq']) ? $type : null;

        if ($type) {
            $grid->model()->where('type', $type);
        }

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new DataType());
        });

        return $type;
    }

    public static function keywordLabelModal($grid, $field, $name)
    {
        $grid->column($field, $name)->display(function ($val) {
            $labels = explode(',', $val);
            $count  = count($labels);
            return '共有' . $count . '个匹配词';
        })->modal($name . '-列表', function ($model) use ($field) {
            $values = $model[$field];
            $labels = explode(',', $values);

            $string = collect($labels)->map(function ($label) {
                return "<h4 style='display: inline-block;margin-right:5px;'><span class=\" label label-success\">$label</span></h4>";
            })->join("");
            return $string;
        });
    }

    public function appendDepartmentType(Grid $grid)
    {
        $type = request()->get('type');
        $id   = DepartmentType::all()->pluck('id');
        $type = in_array($type, $id->toArray()) ? $type : null;

        if ($type) {
            $grid->model()->where('department_id', $type);
        }
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new DepartmentDataType());
        });
        return $type;
    }

    public function appendFormType(Grid $grid, $field = 'form_type')
    {
        $type = request()->get('form_type');

        $type = in_array($type, array_keys(FormData::$FormTypeList)) ? $type : null;

        if ($type) {
            $grid->model()->where($field, $type);
        }

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new FormTypeFilter());
        });

        return $type;

    }

    /**
     * 将 筛选 渠道按钮加入页面
     * @param Grid   $grid
     * @param string $field
     * @return mixed|null
     */
    public function appendChannelType(Grid $grid, $field = 'channel_id')
    {
        $type = request()->get($field);

        if ($type && $type != 'all') {
            $grid->model()->where($field, $type);
        }

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new ChannelTypeFilter());
        });

        return $type;

    }


}
