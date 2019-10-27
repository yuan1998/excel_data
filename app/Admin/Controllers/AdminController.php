<?php

namespace App\Admin\Controllers;

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

    public function initVue()
    {
        Admin::script(<<<EOF
        const app = new Vue({
            el: '#app'
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

}
