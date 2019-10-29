<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeiboUserController extends Controller
{

    public function loginPage() {
        dd(Auth::guard('weibo')->check());
        return view('admin.weibo.login');
    }

}
