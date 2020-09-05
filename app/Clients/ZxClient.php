<?php

namespace App\Clients;

class ZxClient extends BaseClient
{
    public static $type = 'zx';
    public static $cookie_name = '172.16.8.880_AdminContext_';
    public static $base_url = 'http://172.16.8.8/';
    public static $domain = '172.16.8.8';
    public static $companyApi = false;
    public static $mediaSourceType = '9295C7B6F93E4E51A9C09E1C2198CCB5';

    public static $account = [
        'username' => '7010',
        'password' => 'hm2020',
    ];

    public static $baseAccount = [
        'username' => '7010',
        'password' => 'hm2018',
    ];

}
