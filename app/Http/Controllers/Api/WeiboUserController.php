<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\WeiboLoginRequest;
use App\Models\WeiboUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeiboUserController extends Controller
{

    public function userPause(Request $request)
    {
        $user = $this->weiboUser();

        if (!$user) {
            $this->response->errorUnauthorized();
        }

        $pause       = $request->get('pause');
        $user->pause = !!$pause;
        $user->save();

        return $user;
    }


    public function updatePause(Request $request)
    {
        $pause = $request->get('pause');
        $id    = $request->get('id');
        if (!$id) {
            $this->response->errorBadRequest();
        }
        $weibo        = WeiboUser::find($id);
        $weibo->pause = !!$pause;
        $weibo->save();

        return $this->response->array([
            'data' => $weibo
        ]);
    }

}
