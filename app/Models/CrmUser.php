<?php

namespace App\Models;

use App\Clients\ZxClient;
use Illuminate\Database\Eloquent\Model;

class CrmUser extends Model
{
    protected $fillable = [
        'name',
        'crm_id',
        'user_number',
    ];

    /**
     * 抓取 Crm系统 里的客服数据.
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\CurlException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    public static function GrabCrmUserData()
    {
        // 获取dom 数据
        $dom  = ZxClient::tempSearchApi(['phone' => 123]);
        $data = $dom->find('#CreatedBy option');

        foreach ($data as $item) {
            // 获取value (Crm_ID)
            $value = $item->getAttribute('value');
            if (!$value) continue;
            // 将 名称 与 编号 分开
            preg_match('/(.*)\((.*?)\)/', $item->innerHtml, $matches);

            // 更新或生成 Crm客服
            static::updateOrCreate([
                'name' => $matches[1],
            ], [
                'user_number' => $matches[2],
                'crm_id'      => $value
            ]);
        }
        return count($data);
    }
}
