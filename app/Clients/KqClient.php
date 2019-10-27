<?php

namespace App\Clients;

class KqClient extends BaseClient
{
    public static $type = 'kq';
    public static $cookie_name = '172.16.8.88999_AdminContext_';
    public static $base_url = 'http://172.16.8.8:8999/';
    public static $domain = '172.16.8.8';

    public static $account = [
        'username' => '7002',
        'password' => 'ty123',
    ];

}
