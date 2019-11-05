<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DispatchRuleRequest;
use App\Http\Requests\PostWeiboSettingRequest;
use App\Models\WeiboDispatchSetting;
use Illuminate\Http\Request;

class WeiboDispatchSettingController extends Controller
{

    public $modelName = '\\App\\Models\\WeiboDispatchSetting';


    /**
     * 保存 基础分配配置.
     * @param PostWeiboSettingRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function saveSetting(PostWeiboSettingRequest $request)
    {
        $type = $request->get('setting_type');
        $data = $request->get('setting_data');

        WeiboDispatchSetting::setBaseSettings($type, $data);

        return $this->response->noContent();
    }

    /**
     * 保存 高级分配配置
     * @param DispatchRuleRequest $request
     * @return mixed
     */
    public function ruleStore(DispatchRuleRequest $request)
    {
        $data = $request->all();

        $item = WeiboDispatchSetting::create($data);

        return $this->response->array($item->toArray());
    }

    public function ruleUpdate(WeiboDispatchSetting $dispatchSetting, DispatchRuleRequest $request)
    {
        $data = $request->all();
        $dispatchSetting->update($data);
        return $this->response->array($dispatchSetting->toArray());
    }


}
