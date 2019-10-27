<?php

namespace App\Admin\Extensions\Tools;

use App\Models\DepartmentType;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class DepartmentDataType extends AbstractTool
{
    protected function script()
    {
        $url = Request::fullUrlWithQuery(['type' => '_type_']);

        return <<<EOT

$('input:radio.user-type').change(function () {

    var url = "$url".replace('_type_', $(this).val());

    $.pjax({container:'#pjax-container', url: url });

});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());
        $departmentOptions = DepartmentType::all()->pluck('title', 'id')->toArray();
        $options           = [
                'all' => '全部',
            ] + $departmentOptions;

        return view('admin.tools.type', compact('options'));
    }
}
