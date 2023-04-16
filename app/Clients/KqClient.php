<?php

namespace App\Clients;

class KqClient extends BaseClient
{
    public static $type = 'kq';
    public static $cookie_name = '172.16.8.180_AdminContext_';
    public static $base_url = 'http://172.16.8.1/';
    public static $domain = '172.16.8.1';

    public static $account = [
        'username' => '7002',
        'password' => 'ty123',
    ];

}
