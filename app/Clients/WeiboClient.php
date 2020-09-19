<?php

namespace App\Clients;

use App\Models\WeiboAccounts;
use App\Models\WeiboFormData;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PHPHtmlParser\Dom;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WeiboClient
{

    public static $Account = [
        '口腔'  => [
            'username'    => '17392448796',
            'password'    => 'huamei2019',
            'customer_id' => '6660030357',
            'site_id'     => '7510',
            'form_id'     => '7226',
            'type'        => 'kq',
        ],
        '团圆'  => [
            'username'    => '17392449035',
            'password'    => 'huamei2019',
            'customer_id' => '7165564518',
            'type'        => 'kq',
        ],
        '整形'  => [
            'username'    => '18092693627',
            'password'    => 'huamei123',
            'customer_id' => '6216702497',
            'type'        => 'zx',
        ],
        '罗金刚' => [
            'username'    => '17391917587',
            'password'    => 'huamei123',
            'customer_id' => '1043344731',
            'type'        => 'zx',
        ],
    ];

    public $accountId;
    public $account;
    public $client;

    /**
     * WeiboClient constructor.
     * @param      $accountId
     * @param null $account
     */
    public function __construct($accountId, $account = null)
    {
        $this->accountId = $accountId;
        $this->account   = $account ?? WeiboAccounts::find($accountId);
    }

    public static function getWeiboSu($username)
    {
        $cmd = base_path('PythonScript/weiboRSA.py');

        $process = new Process(['python3', $cmd, $username]);
        $process->run();

        if ($process->isSuccessful()) {
            $data = $process->getOutput();
            $data = json_decode($data);
            return $data;
        }

        return null;
    }

    public static function RSAWeiboPassword($json)
    {
        $cmd     = base_path('PythonScript/weiboRSAPassword.py');
        $process = new Process(['python3', $cmd, $json]);
        $process->run();

        if ($process->isSuccessful() && $item = json_decode($process->getOutput(), true)) {
            return $item['password'];
        } else {
            throw new ProcessFailedException($process);

        }

        return null;
    }

    public function checkIdCookieFile()
    {
        $id = $this->accountId;
        if ($id)
            return Storage::disk('public')->exists("weibo_cookie/{$id}-cookies.json");

        return false;
    }

    public function deleteIdCookieFile()
    {
        $id = $this->accountId;
        Storage::disk('public')->delete("weibo_cookie/{$id}-cookies.json");
    }

    public function getCookieFilePath()
    {
        return Storage::disk('public')->path("weibo_cookie/{$this->accountId}-cookies.json");
    }

    public function getIdCookie()
    {
        $id = $this->accountId;
        if ($id) {
            return new FileCookieJar($this->getCookieFilePath(), true);
        }

        return false;
    }

    public function getClient()
    {
        if (!$this->client) {
            $jar = $this->getIdCookie();

            $this->client = new Client([
                'cookies'     => $jar,
                'verify'      => false,
                'http_errors' => false,
                'headers'     => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36'
                ],
            ]);
        }

        return $this->client;

    }

    public function mapQrCodeToGet()
    {
        $client = $this->getClient();

        $key = time() * 1000000;
        $url = "http://login.sina.com.cn/sso/qrcode/image?entry=weibo&size=180&callback=STK_" . $key;

        $res = $client->get($url);
        return $res->getBody()->getContents();
    }

    public function mapQrCodeToScan($qrId)
    {
        $qrcodeCheck = "https://login.sina.com.cn/sso/qrcode/check";
        $params      = [
            "entry"    => "weibo",
            "qrid"     => $qrId,
            "callback" => "STK_" . (time() * 100000),
        ];

        $client = $this->getClient();

        $res = $client->request('GET', $qrcodeCheck, [
            'query'   => $params,
            'timeout' => 0,
            'headers' => [
                'Referer' => 'https://weibo.com/'
            ]
        ]);
//        dd($res);
        return $res->getBody()->getContents();
    }

    public function mapQrCodeToLogin($alt)
    {
        $params   = [
            "entry"       => "weibo",
            "returntype"  => "TEXT",
            "crossdomain" => 1,
            "cdult"       => 3,
            "domain"      => "weibo.com",
            "alt"         => $alt,
            "savestate"   => 30,
            "callback"    => "STK_" . (time() * 1000000)
        ];
        $loginUrl = "http://login.sina.com.cn/sso/login.php";
        $client   = $this->getClient();

        $res = $client->request('GET', $loginUrl, [
            'query'   => $params,
            'timeout' => 0,
            'headers' => [
                'Referer' => 'https://weibo.com/'
            ]
        ]);
        return $res->getBody()->getContents();
    }

    public function mapClientToPreLogin()
    {
        $su = static::getWeiboSu($this->account->username);

        if (!$su) return false;

        $data   = [
            'entry'    => 'account',
            'callback' => 'sinaSSOController.preloginCallBack',
            'su'       => $su,
            'rsakt'    => 'mod',
            'client'   => 'ssologin.js(v1.4.19)',
            '_'        => time() * 1000,
        ];
        $client = static::getClient();

        $response = $client->request('GET', 'https://login.sina.com.cn/sso/prelogin.php', [
            'query'   => $data,
            'headers' => [
                'Referer' => 'https://login.sina.com.cn/signup/signin.php',
            ]
        ]);
        $body     = $response->getBody()->getContents();
        preg_match("/({.*})/", $body, $matches);

        if ($matches && $item = json_decode($matches[0], true)) {
            if ($item['retcode'] == 0) {
                $item['su'] = $su;
                return $item;
            }
        }

        return false;
    }

    public function mapClientToLogin()
    {
        $this->deleteIdCookieFile();

        $serverData = $this->mapClientToPreLogin();
        if (!$serverData) return false;

        $json = [
            'pubkey'     => $serverData['pubkey'],
            'servertime' => $serverData['servertime'],
            'nonce'      => $serverData['nonce'],
            'password'   => $this->account->password,
        ];

        $RSAPassword = static::RSAWeiboPassword(json_encode($json));

        $params = [
            "entry"       => 'account',
            "gateway"     => '1',
            "from"        => '',
            "savestate"   => '30',
            'qrcode_flag' => 'true',
            'useticket'   => '1',
            "pagerefer"   => 'https://sina.com.cn/',
            "vsnf"        => '1',
            "su"          => $serverData['su'],
            "service"     => 'sso',
            "servertime"  => $serverData['servertime'],
            "nonce"       => $serverData['nonce'],
            "pwencode"    => 'rsa2',
            "rsakv"       => $serverData['rsakv'],
            "sp"          => $RSAPassword,
            "sr"          => '2560*1440',
            "encoding"    => 'UTF-8',
            "cdult"       => '3',
            "domain"      => 'sina.com.cn',
            "prelt"       => '43',
            "returntype"  => 'TEXT',
        ];
        $time   = time() * 1000;

        $client = $this->getClient();

        $uri      = "https://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.15)&_={$time}";
        $res      = $client->request('POST', $uri, [
            'form_params' => $params,
            'headers'     => [
                'Referer'      => 'https://login.sina.com.cn/signup/signin.php',
                "Content-Type" => 'application/x-www-form-urlencoded',
                'Host'         => 'login.sina.com.cn',
                'Origin'       => 'https://login.sina.com.cn',
            ],
        ]);
        $body     = $res->getBody()->getContents();
        $response = json_decode($body, true);
        if ($response && $response['retcode'] == 0 && $response['crossDomainUrlList']) {
            $this->crossDomainLogin($response['crossDomainUrlList']);
        }

        return $this->isLogin(false);
    }

    public function crossDomainLogin($urlList)
    {

        $result = [];
        $client = $this->getClient();
        foreach ($urlList as $url) {
            $res      = $client->request("GET", $url);
            $result[] = $res->getHeader('Set-Cookie');
            usleep(500 * 1000);
        }

        return $result;
    }

    public function isLogin($checkFile = true)
    {
        if ($checkFile && !$this->checkIdCookieFile())
            return false;

        $client = $this->getClient();

        $response = $client->request('GET', 'https://weibo.com/');
        $ctx      = $response->getBody()->getContents();

        return !!preg_match('/\$CONFIG\[\'uid\'\]=\'\d+\'/', $ctx);
    }

    public function mapFormListToGet($customerId, $start, $end, $count = 1000, $page = 1)
    {
        $params = [
            "page"        => $page,
            "page_size"   => $count,
            "time_order"  => "",
            "feed_type"   => "",
            "group_id"    => "",
            "page_name"   => "",
            "customer_id" => $customerId,
            "time_start"  => $start,
            "time_end"    => $end,
        ];
        $url    = "https://cpl.biz.weibo.com/cpl/lead/list";

        $client = $this->getClient();
        $res    = $client->request('GET', $url, [
            'query'   => $params,
            'timeout' => 0,
        ]);
        $body   = $res->getBody()->getContents();
        $data   = json_decode($body, true);

        if ($data && isset($data['code']) && $data['code'] === 10000) {
            $dataResult = $data['result'];
            $list       = collect($dataResult['data'])->map(function ($item) {
                return WeiboFormData::parseCPLListData($item);
            });
            return [
                'code'  => 0,
                'list'  => $list,
                'total' => (int)$dataResult['total'],
            ];
        }
        return false;
    }

    public function mapLingDongFormListToGet($customerId, $start, $end, $count = 1000, $page = 1)
    {
        $params = [
            "source"      => 0,
            "mark_status" => 0,
            "page"        => $page,
            "page_size"   => 50,
            "start_date"  => Carbon::parse($start)->toDateString(),
            "end_date"    => Carbon::parse($end)->toDateString(),
            "customer_id" => $customerId,
            '_t'          => (time() * 1000000),
        ];


        $url = "https://lingdong.biz.weibo.com/form-data/list";

        $client = $this->getClient();
        $res    = $client->request('GET', $url, [
            'query'   => $params,
            'timeout' => 0,
            'headers' => [
                'Referer' => 'https://lingdong.biz.weibo.com/',
            ]
        ]);
        $body   = $res->getBody()->getContents();
        $data = json_decode($body, true);

        if ($data && isset($data['code']) && $data['code'] === 0) {
            $result = $data['data'];
            $list   = collect($result['list'])->map(function ($item) {
                return WeiboFormData::parseLingDongListData($item);
            });
            return [
                'code'  => 0,
                'list'  => $list,
                'total' => (int)$result['total'],
            ];
        }

        return false;
    }
}
