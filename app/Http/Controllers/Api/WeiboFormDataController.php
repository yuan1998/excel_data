<?php

namespace App\Http\Controllers\Api;

use App\Clients\WeiboClient;
use App\Helpers;
use App\Models\WeiboAccounts;
use App\Models\WeiboFormData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeiboFormDataController extends Controller
{


    public function pullWeiboFormData(Request $request)
    {
        $dates = $request->get('dates');
        if (!$dates || !is_array($dates)) $this->response->errorBadRequest('错误的参数,无法识别的日期');

        $accountID = $request->get('account_id');
        $account   = WeiboAccounts::find($accountID);

        if (!$account)
            return $this->response->array([
                'status' => 0,
                'msg'    => '错误,账户不存在',
            ]);

        $startDate = $dates[0];
        $endDate   = $dates[1];
        $result    = [];
        if ($account->enable_cpl)
            $result[WeiboAccounts::$_CPL_NAME_] = $account->pullFormDataOfType(WeiboAccounts::$_CPL_NAME_, $startDate, $endDate);
        if ($account->enable_lingdong)
            $result[WeiboAccounts::$_LINGDONG_NAME_] = $account->pullFormDataOfType(WeiboAccounts::$_LINGDONG_NAME_, $startDate, $endDate);


        if (count($result) === 0) {
            return $this->response->array([
                'status' => 0,
                'msg'    => '请打开需要抓取的数据开关.',
            ]);
        }

        $resultStr = collect($result)->map(function ($value, $key) {
            $name  = WeiboAccounts::$FormTypeName[$key];
            $value = $value === false ? '拉取错误' : '一个拉取了' . $value . '条数据';
            return "{$name} : $value";
        })->join('<br>');

        return $this->response->array([
            'status' => 1,
            'msg'    => '获取数据成功.<br>' . $resultStr,
        ]);


    }

    public function userHasNew(Request $request)
    {
        $user = $this->weiboUser();

        if (!$user) {
            $this->response->errorUnauthorized();
        }

        $date = $request->get('date');

        $count = WeiboFormData::query()
            ->where('weibo_user_id', $user->id)
            ->where('dispatch_date', '>', $date)
            ->whereNull('recall_date')
            ->count();

        return $this->response->array([
            'count'  => $count,
            'status' => true,
        ]);
    }


    public function userIndex()
    {

        $user = $this->weiboUser();

        if (!$user) {
            $this->response->errorUnauthorized();
        }

        $data = WeiboFormData::query()
            ->with([
                'recallLog' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }, 'recallLog.changeBy'
            ])
            ->userIndex($user)
            ->orderBy('upload_date', 'desc')
            ->paginate();

        $item = WeiboFormData::query()
            ->where('weibo_user_id', $user->id)
            ->orderBy('dispatch_date', 'desc')
            ->first();

        $last_date = $item ? (string)$item->dispatch_date : Carbon::now()->toDateTimeString();

        return $this->response->array([
            'data'             => $data,
            'last_upload_date' => $last_date,
            'status'           => true,
        ]);
    }

    public function userUpdate($id, Request $request)
    {
        $user = $this->weiboUser();

        if (!$user) {
            $this->response->errorUnauthorized();
        }
        $formData = WeiboFormData::find($id);
        if ($formData->weibo_user_id != $user->id) {
            $this->response->errorBadRequest('不属于该用户的表单');
        }
        $formData->update($request->all());
        return $this->response->array([
            'data'   => WeiboFormData::query()->with([
                'recallLog' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }, 'recallLog.changeBy'
            ])->find($id),
            'status' => true
        ]);
    }

}
