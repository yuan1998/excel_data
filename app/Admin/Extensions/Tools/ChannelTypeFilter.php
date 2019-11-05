<?php

namespace App\Admin\Extensions\Tools;

use App\Models\Channel;
use App\Models\FormData;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class ChannelTypeFilter extends AbstractTool
{
    protected function script()
    {
        $url = Request::fullUrlWithQuery(['channel_id' => '_channel_id_']);

        return <<<EOT

$('input:radio.user-type').change(function () {
    var url = "$url".replace('_channel_id_', $(this).val());
    $.pjax({container:'#pjax-container', url: url });
});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());
        $options = Channel::all()->pluck('title', 'id')->toArray();

        $options = ['all' => '全部'] + $options;

        return view('admin.tools.ChannelType', compact('options'));
    }
}
