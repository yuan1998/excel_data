<?php

namespace App\Clients;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WeiboClient
{

    public static $Account = [
        '口腔'  => [
            'username' => '17392448796',
            'password' => 'huamei2019',
            'customer_id' => '6660030357',
            'type'     => 'kq',
        ],
        '团圆'  => [
            'username' => '17392449035',
            'password' => 'huamei2019',
            'customer_id' => '7165564518',
            'type'     => 'kq',
        ],
        '整形'  => [
            'username' => '18092693627',
            'password' => 'huamei123',
            'customer_id' => '6216702497',
            'type'     => 'zx',
        ],
        '罗金刚' => [
            'username' => '17391917587',
            'password' => 'huamei123',
            'customer_id' => '1043344731',
            'type'     => 'zx',
        ],
    ];


    public static function getWeiboData($account, $startDate, $endDate, $count = 2000)
    {

        $cmd     = base_path('PythonScript/weibo_test.py');
        $process = new Process([
            'python3',
            $cmd,
            $startDate,
            $endDate,
            $count,
            $account['username'],
            $account['password'],
            $account['customer_id'],
        ]);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        try {
            $test  = json_decode($process->getOutput(), true);
            $total = $test['result']['total'];
            if ($total > $count) {
                return static::getWeiboData($startDate, $endDate, $total);
            }
            $data = $test['result']['data'];
            return $data;
        } catch (\Exception $exception) {
            Log::info('getWeiboData Exception', [$exception->getMessage()]);
        }
        return null;
    }
}
