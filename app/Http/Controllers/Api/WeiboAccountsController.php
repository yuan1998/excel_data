<?php

namespace App\Http\Controllers\Api;

use App\Models\WeiboAccounts;
use Illuminate\Http\Request;

class WeiboAccountsController extends Controller
{

    public function syncAccounts()
    {
        WeiboAccounts::setAccountData();
        return response()->noContent();
    }
}
