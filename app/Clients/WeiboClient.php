<?php

namespace App\Clients;

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
    public $client;

    /**
     * WeiboClient constructor.
     * @param $accountId
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;
    }

    public function checkIdCookieFile()
    {
        $id = $this->accountId;
        if ($id)
            return Storage::disk('public')->exists("weibo_cookie/{$id}-cookies.json");

        return false;
    }

    public function getIdCookie()
    {
        $id = $this->accountId;
        if ($id) {
            return new FileCookieJar(Storage::disk('public')->path("weibo_cookie/{$id}-cookies.json"), true);
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
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0'
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

    public function isLogin()
    {
        $checkIdCookieFile = $this->checkIdCookieFile();
        if (!$checkIdCookieFile)
            return false;

        $client = $this->getClient();

        $response = $client->request('GET', 'https://weibo.com/');
        $ctx      = $response->getBody()->getContents();

        return !!preg_match('/\$CONFIG\[\'uid\'\]=\'\d+\'/', $ctx);
    }

    public function mapFormListToGet($customerId, $start, $end, $count = 1000 , $page = 1)
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


        return (json_last_error() == JSON_ERROR_NONE && $data && isset($data['code']))
            ? $data
            : false;
    }
}
