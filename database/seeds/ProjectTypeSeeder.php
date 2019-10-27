<?php

use App\Models\ProjectType;
use Illuminate\Database\Seeder;

class ProjectTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now()->toDateTime();


        ProjectType::insert([
            [
                'title'      => '双眼皮',
                'type'       => 'zx',
                'keyword'    => '双眼皮',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '眼袋',
                'type'       => 'zx',
                'keyword'    => '眼袋',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '隆鼻',
                'type'       => 'zx',
                'keyword'    => '隆鼻,鼻部',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '吸脂',
                'type'       => 'zx',
                'keyword'    => '吸脂',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '脂肪',
                'type'       => 'zx',
                'keyword'    => '脂肪',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '私密',
                'type'       => 'zx',
                'keyword'    => '私密',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '面部',
                'type'       => 'zx',
                'keyword'    => '面部',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'title'      => '美肤祛斑',
                'type'       => 'zx',
                'keyword'    => '祛斑',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '祛痘换肤',
                'type'       => 'zx',
                'keyword'    => '祛痘',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '除皱',
                'type'       => 'zx',
                'keyword'    => '除皱',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '疤痕',
                'type'       => 'zx',
                'keyword'    => '疤痕',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '妊娠纹',
                'type'       => 'zx',
                'keyword'    => '妊娠纹',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '医学纹绣',
                'type'       => 'zx',
                'keyword'    => '纹绣',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '脱毛',
                'type'       => 'zx',
                'keyword'    => '脱毛',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '面部年轻化',
                'type'       => 'zx',
                'keyword'    => '面部年轻化',
                'created_at' => $now,
                'updated_at' => $now,
            ],


            [
                'title'      => '种植',
                'type'       => 'kq',
                'keyword'    => '种植,种牙',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => '矫正',
                'type'       => 'kq',
                'keyword'    => '矫正,矫牙,西安画美团圆口腔医院,西安画美口腔医院',
                'created_at' => $now,
                'updated_at' => $now,
            ],


        ]);
    }
}
