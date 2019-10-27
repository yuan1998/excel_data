<?php
namespace App\Clients;

class ZxClient extends BaseClient {
    public static $type = 'zx';
    public static $cookie_name = '172.16.8.880_AdminContext_';
    public static $base_url = 'http://172.16.8.8/';
    public static $domain = '172.16.8.8';

    public static $account = [
        'username' => '7010',
        'password' => 'hm2018',
    ];

}
