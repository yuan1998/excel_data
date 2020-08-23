<?php

namespace App\Http\Controllers\Api;

use App\Clients\WeiboClient;
use App\Jobs\WeiboAccountClientLoginJob;
use App\Models\WeiboAccounts;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;

class WeiboAccountsController extends Controller
{

    public function syncAccounts()
    {
        WeiboAccounts::setAccountData();
        return response()->noContent();
    }

    public function getQrCode(Request $request)
    {
        $accountId   = $request->get('account_id');
        $weiboClient = new WeiboClient($accountId);

        if (!$weiboClient->account)
            return $this->response->array([
                'code' => 1003,
                'msg'  => '错误的账户ID'
            ]);

        $body = $weiboClient->mapQrCodeToGet();
        preg_match("/({.*})/", $body, $matches);

        if ($matches && $item = json_decode($matches[0], true)) {
            if ($item['msg'] === 'succ') {
                return $this->response->array([
                    'code' => 0,
                    'data' => $item['data']
                ]);
            }
        }

        return $this->response->array([
            'code' => 10001,
            'msg'  => '请求二维码错误!'
        ]);
    }

    public function scanQrCode(Request $request)
    {
        $qrId      = $request->get('qrid');
        $accountId = $request->get('account_id');

        $weiboClient = new WeiboClient($accountId);

        if (!$weiboClient->account)
            return $this->response->array([
                'code' => 1003,
                'msg'  => '错误的账户ID'
            ]);

        $body = $weiboClient->mapQrCodeToScan($qrId);

        preg_match("/({.*})/", $body, $matches);
        if ($matches && $item = json_decode($matches[0], true)) {
            return $this->response->array([
                'code' => 0,
                'data' => $item
            ]);
        }
        return $this->response->array([
            'code' => 10001,
            'msg'  => '扫描接口错误!'
        ]);
    }

    public function loginQrCode(Request $request)
    {
        $alt       = $request->get('alt');
        $accountId = $request->get('account_id');

        $account = WeiboAccounts::find($accountId);

        if (!$account)
            return $this->response->array([
                'code' => 1003,
                'msg'  => '错误的ID'
            ]);

        $weiboClient = new WeiboClient($accountId);
        $body        = $weiboClient->mapQrCodeToLogin($alt);
        preg_match("/({.*})/", $body, $matches);

        if ($matches && $item = json_decode($matches[0], true)) {
            if ($item['retcode'] == 0 && $item['crossDomainUrlList']) {
                $weiboClient->crossDomainLogin($item['crossDomainUrlList']);

                if ($weiboClient->isLogin()) {
                    WeiboAccounts::query()
                        ->where('id', $accountId)
                        ->update([
                            'login_status' => 1,
                        ]);
                    return $this->response->array([
                        'code' => 0,
                        'msg'  => '登录成功',
                    ]);
                }

            }
        }
        return $this->response->array([
            'code' => 10001,
            'msg'  => '登录接口错误,请联系管理员!'
        ]);
    }

    public function isLogin(Request $request)
    {
        $id          = $request->get('account_id');
        $weiboClient = new WeiboClient($id);

        if (!$weiboClient->account)
            return $this->response->array([
                'code' => 1001,
                'msg'  => '错误的账户Id'
            ]);


        if ($weiboClient->isLogin()) {
            return $this->response->array([
                'code' => 0,
                'msg'  => '已登录',
            ]);
        }

        $weiboClient->account->login_status = 0;
        $weiboClient->account->save();

        return $this->response->array([
            'code' => 1000,
            'msg'  => '未登录或者登陆已过期,请重新登录',
        ]);
    }

    public function cplDataList()
    {
        $weiboClient = new WeiboClient(1);
        $data        = $weiboClient->mapFormListToGet('6660030357', '2020-08-13', '2020-08-13');

        if (!$data)
            return $this->response->array([
                'code' => 10005,
                'msg'  => '获取表单数据失败.'
            ]);

        if ($data['code'] === 10000)
            return $this->response->array([
                'code' => 0,
                'data' => $data['result']
            ]);

        return $this->response->array([
            'code' => 10006,
            'msg'  => '数据结果在意料之外',
            'data' => $data
        ]);
    }

    public function jobLoginClient(Request $request)
    {
        $id = $request->get('account_id');
        if (!WeiboAccounts::query()->where('id', $id)->exists())
            return $this->response->array([
                'code' => 1001,
                'msg'  => '错误的账户Id'
            ]);

        WeiboAccountClientLoginJob::dispatch($id)->onQueue('pull_weibo_data');

        return $this->response->array([
            'code' => 0,
            'msg'  => '登录成功'
        ]);
    }

    public function loginClient(Request $request)
    {
        $id          = $request->get('account_id');
        $weiboClient = new WeiboClient($id);

        if (!$weiboClient->account)
            return $this->response->array([
                'code' => 1001,
                'msg'  => '错误的账户Id'
            ]);

        $loginStatus = $weiboClient->mapClientToLogin();

        $weiboClient->account->login_status = $loginStatus ? 1 : 0;
        $weiboClient->account->save();

        if ($loginStatus) {
            return $this->response->array([
                'code' => 0,
                'msg'  => '登录成功'
            ]);
        } else {
            return $this->response->array([
                'code' => 10001,
                'msg'  => '登录失败'
            ]);
        }


    }

}
