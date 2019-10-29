<?php

namespace App\Http\Controllers\Api;

use App\Models\WeiboFormData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeiboFormDataController extends Controller
{

    public function userHasNew(Request $request)
    {
        $user = $this->weiboUser();

        if (!$user) {
            $this->response->errorUnauthorized();
        }

        $date = $request->get('date');

        $count = WeiboFormData::query()
            ->where('weibo_user_id', $date)
            ->whereDate('upload_date', '>', $date)
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
            ->where('weibo_user_id', $user->id)
            ->orderBy('upload_date', 'desc')
            ->paginate();

        $item = WeiboFormData::query()
            ->where('weibo_user_id', $user->id)
            ->orderBy('upload_date', 'desc')
            ->first();

        $last_date = $item ? $item->upload_date : Carbon::now()->toDateTimeString();

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
        $data = $request->all();

        if (isset($data['comment'])) {
            $data['recall_date'] = Carbon::now()->toDateTimeString();
        }
        $formData->fill($data);
        $formData->save();
        return $this->response->array([
            'data'   => $formData,
            'status' => true
        ]);
    }

}
