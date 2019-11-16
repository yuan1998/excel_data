<?php

use App\Models\AccountData;
use App\Models\Channel;
use Illuminate\Database\Seeder;

class AccountDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name'        => '微博-整形-默认',
                'rebate'      => '1.3',
                'is_default'  => 1,
                'keyword'     => '',
                'crm_keyword' => '',
                'type'        => 'zx',
                'channel'     => '微博',
            ],
            [
                'name'        => '微博-口腔-默认',
                'rebate'      => '1.3',
                'is_default'  => 1,
                'keyword'     => '',
                'crm_keyword' => '',
                'type'        => 'kq',
                'channel'     => '微博',
            ],

            // 抖音
            [
                'name'        => '抖音-口腔-D11',
                'keyword'     => 'D11',
                'crm_keyword' => 'D11',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '抖音',
            ],
            [
                'name'        => '抖音-口腔-D14',
                'keyword'     => 'D14',
                'crm_keyword' => 'D14',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '抖音',
            ],
            [
                'name'        => '抖音-口腔-D15',
                'keyword'     => 'D15',
                'crm_keyword' => 'D15',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '抖音',
            ],
            [
                'name'        => '抖音-口腔-D7',
                'keyword'     => 'D7',
                'crm_keyword' => 'D7',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '抖音',
            ],
            [
                'name'        => '抖音-口腔-D9',
                'keyword'     => 'D9',
                'crm_keyword' => 'D9',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '抖音',
            ],

            // 头条
            [
                'name'        => '头条-口腔-B17',
                'keyword'     => 'B17',
                'crm_keyword' => 'B17',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '头条',
            ],
            [
                'name'        => '头条-口腔-B18',
                'keyword'     => 'B18',
                'crm_keyword' => 'B18',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '头条',
            ],
            [
                'name'        => '头条-口腔-B22',
                'keyword'     => 'B22',
                'crm_keyword' => 'B22',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '头条',
            ],
            [
                'name'        => '头条-口腔-B5',
                'keyword'     => 'B5',
                'crm_keyword' => 'B5',
                'rebate'      => '1.3',
                'type'        => 'kq',
                'channel'     => '头条',
            ],
        ];

        AccountData::truncate();
        AccountData::reguard();
        foreach ($data as $item) {
            $channel = Channel::query()->where('title', 'like', "%{$item['channel']}%")->first();
            if ($channel) {
                $item['channel_id'] = $channel->id;
                AccountData::create($item);
            }
        }
    }
}
