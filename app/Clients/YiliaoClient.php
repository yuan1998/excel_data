<?php

namespace App\Clients;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class YiliaoClient
{

    public static function getYiliaoData($startDate, $endDate)
    {
        $cmd     = base_path('PythonScript/yiliao.py');
        $process = new Process([
            'python3',
            $cmd,
            $startDate,
            $endDate,
        ]);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        try {
            $test = json_decode($process->getOutput(), true);
            dd(123,$test);
            return $test;
        } catch (\Exception $exception) {
            Log::info('抓取易聊数据时出错', [$exception->getMessage()]);
        }
        return null;
    }

}
