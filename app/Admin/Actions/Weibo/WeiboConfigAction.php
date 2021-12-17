<?php

namespace App\Admin\Actions\Weibo;

use App\Helpers;
use App\Imports\BaiduImport;
use App\Imports\FeiyuImport;
use App\Imports\WeiboImport;
use App\Models\Channel;
use App\models\CrmGrabLog;
use App\Models\DepartmentType;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class WeiboConfigAction extends Action
{

    public static $defaultFormData = [
        'stop_open'       => true,
        'stop_open_start' => '22:00:00',
        'stop_open_end'   => '09:00:00',
        'dispatch_start'  => true,
    ];

    /**
     * ExcelUpload constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(Request $request)
    {
        $data = $request->only(['stop_open', 'stop_open_start', 'stop_open_end', 'dispatch_start']);
        static::setFormConfig($data);
        return $this->response()->success('设置成功,马上生效.');
    }

    public static function getFormConfig()
    {
        $data                       = Redis::get('weibo_form_data_config');
        $formData                   = array_merge(static::$defaultFormData, $data ? json_decode($data, true) : []);
        $formData['stop_open']      = $formData['stop_open'] === 'true' || $formData['stop_open'] === true;
        $formData['dispatch_start'] = $formData['dispatch_start'] === 'true' || $formData['dispatch_start'] === true;
        return $formData;
    }

    public static function setFormConfig($data = [])
    {
        $data = json_encode(array_merge(static::$defaultFormData, $data));
        Redis::set('weibo_form_data_config', $data);
    }

    public function render()
    {
        $formData = static::getFormConfig();

        return view('admin.actions.weiboConfigAction', [
            'formData' => $formData
        ]);
    }
}
