<?php

namespace App\Http\Controllers\Api;

use App\Clients\WeiboClient;
use App\Helpers;
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
        
        $result = WeiboFormData::pullWeiboData($accountID, $dates[0], $dates[1]);

        if ($result === null) {
            return $this->response->array([
                'status' => 0,
                'msg'    => '错误,数据获取失败,请呼叫先关人员',
            ]);
        } else {
            return $this->response->array([
                'status' => 1,
                'msg'    => '获取数据成功,一共获取了' . $result . '条数据.',
            ]);
        }

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
