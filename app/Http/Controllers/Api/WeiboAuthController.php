<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\WeiboLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeiboAuthController extends Controller
{

    public function current()
    {
        $user = $this->weiboUser();

        if (!$user) {
            $this->response->errorUnauthorized('登录验证错误');
        }

        return $this->response->array([
            'data'   => $user,
            'status' => true,
        ]);

    }

    public function authenticate(WeiboLoginRequest $request)
    {
        $data = $request->only(['username', 'password']);

        if (!$token = Auth::guard('weibo')->attempt($data)) {
            $this->response->errorUnauthorized('用户名或密码错误');
        }

//        dd(auth('weibo')->setToken($token)->user());

        return $this->respondWithToken($token);
    }

    public function refresh()
    {
        $token = Auth::guard('weibo')->refresh();
        return $this->respondWithToken($token);
    }

    public function destroyToken()
    {
        Auth::guard('weibo')->logout();
        return $this->response->noContent();
    }


}
