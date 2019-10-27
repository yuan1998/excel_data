<?php

namespace App\Admin\Extensions\Tools;

use App\Models\FormData;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class FormTypeFilter extends AbstractTool
{
    protected function script()
    {
        $url = Request::fullUrlWithQuery(['form_type' => '_form_type_']);

        return <<<EOT

$('input:radio.user-type').change(function () {

    var url = "$url".replace('_form_type_', $(this).val());

    $.pjax({container:'#pjax-container', url: url });

});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());


        $options = ['all' => '全部'] + FormData::$FormTypeList;

        return view('admin.tools.formType', compact('options'));
    }
}
