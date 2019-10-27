<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class DataType extends AbstractTool
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

        $options = [
            'all' => '全部',
            'zx'  => '整形',
            'kq'  => '口腔',
        ];

        return view('admin.tools.type', compact('options'));
    }
}
