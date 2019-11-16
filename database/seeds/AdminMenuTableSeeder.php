<?php

use Illuminate\Database\Seeder;

class AdminMenuTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('admin_menu')->delete();
        
        \DB::table('admin_menu')->insert(array (
            0 => 
            array (
                'id' => 1,
                'parent_id' => 0,
                'order' => 1,
                'title' => '仪表盘',
                'icon' => 'fa-bar-chart',
                'uri' => '/',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2019-10-30 11:44:19',
            ),
            1 => 
            array (
                'id' => 2,
                'parent_id' => 0,
                'order' => 24,
                'title' => '系统管理',
                'icon' => 'fa-tasks',
                'uri' => NULL,
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2019-10-30 11:06:00',
            ),
            2 => 
            array (
                'id' => 3,
                'parent_id' => 2,
                'order' => 25,
                'title' => '管理员管理',
                'icon' => 'fa-users',
                'uri' => 'auth/users',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2019-10-30 11:06:00',
            ),
            3 => 
            array (
                'id' => 4,
                'parent_id' => 2,
                'order' => 26,
                'title' => '角色管理',
                'icon' => 'fa-user',
                'uri' => 'auth/roles',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2019-10-30 11:06:00',
            ),
            4 => 
            array (
                'id' => 5,
                'parent_id' => 2,
                'order' => 27,
                'title' => '权限管理',
                'icon' => 'fa-ban',
                'uri' => 'auth/permissions',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2019-10-30 11:06:00',
            ),
            5 => 
            array (
                'id' => 6,
                'parent_id' => 2,
                'order' => 28,
                'title' => '菜单管理',
                'icon' => 'fa-bars',
                'uri' => 'auth/menu',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2019-10-30 11:06:00',
            ),
            6 => 
            array (
                'id' => 7,
                'parent_id' => 2,
                'order' => 29,
                'title' => '操作日志',
                'icon' => 'fa-history',
                'uri' => 'auth/logs',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2019-10-30 11:06:00',
            ),
            7 => 
            array (
                'id' => 8,
                'parent_id' => 37,
                'order' => 3,
                'title' => '表单数据合集',
                'icon' => 'fa-bars',
                'uri' => '/data_collation',
                'permission' => NULL,
                'created_at' => '2019-10-08 15:46:12',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            8 => 
            array (
                'id' => 9,
                'parent_id' => 8,
                'order' => 4,
                'title' => '百度数据',
                'icon' => 'fa-bars',
                'uri' => '/baidu_data',
                'permission' => NULL,
                'created_at' => '2019-10-08 15:46:42',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            9 => 
            array (
                'id' => 10,
                'parent_id' => 8,
                'order' => 5,
                'title' => '微博数据',
                'icon' => 'fa-bars',
                'uri' => '/weibo_data',
                'permission' => NULL,
                'created_at' => '2019-10-08 21:46:05',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            10 => 
            array (
                'id' => 13,
                'parent_id' => 8,
                'order' => 6,
                'title' => '飞鱼数据',
                'icon' => 'fa-bars',
                'uri' => '/feiyu_data',
                'permission' => NULL,
                'created_at' => '2019-10-08 21:58:36',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            11 => 
            array (
                'id' => 16,
                'parent_id' => 27,
                'order' => 17,
                'title' => '病种类型',
                'icon' => 'fa-bars',
                'uri' => '/project_type',
                'permission' => NULL,
                'created_at' => '2019-10-09 17:35:28',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            12 => 
            array (
                'id' => 17,
                'parent_id' => 37,
                'order' => 11,
                'title' => 'CRM数据',
                'icon' => 'fa-bars',
                'uri' => NULL,
                'permission' => NULL,
                'created_at' => '2019-10-09 18:14:11',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            13 => 
            array (
                'id' => 18,
                'parent_id' => 17,
                'order' => 12,
                'title' => '到院数据',
                'icon' => 'fa-bars',
                'uri' => '/arriving_data',
                'permission' => NULL,
                'created_at' => '2019-10-09 18:14:26',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            14 => 
            array (
                'id' => 19,
                'parent_id' => 37,
                'order' => 7,
                'title' => '消费数据',
                'icon' => 'fa-bars',
                'uri' => NULL,
                'permission' => NULL,
                'created_at' => '2019-10-10 17:06:57',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            15 => 
            array (
                'id' => 20,
                'parent_id' => 19,
                'order' => 8,
                'title' => '百度消费',
                'icon' => 'fa-bars',
                'uri' => '/baidu_spend',
                'permission' => NULL,
                'created_at' => '2019-10-10 17:07:15',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            16 => 
            array (
                'id' => 21,
                'parent_id' => 19,
                'order' => 9,
                'title' => '飞鱼消费',
                'icon' => 'fa-bars',
                'uri' => '/feiyu_spend',
                'permission' => NULL,
                'created_at' => '2019-10-10 17:07:40',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            17 => 
            array (
                'id' => 22,
                'parent_id' => 19,
                'order' => 10,
                'title' => '微博消费',
                'icon' => 'fa-bars',
                'uri' => '/weibo_spend',
                'permission' => NULL,
                'created_at' => '2019-10-10 17:42:04',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            18 => 
            array (
                'id' => 23,
                'parent_id' => 27,
                'order' => 18,
                'title' => '渠道类型',
                'icon' => 'fa-bars',
                'uri' => '/channels',
                'permission' => NULL,
                'created_at' => '2019-10-10 22:25:10',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            19 => 
            array (
                'id' => 26,
                'parent_id' => 27,
                'order' => 19,
                'title' => '科室类型',
                'icon' => 'fa-bars',
                'uri' => '/department_type',
                'permission' => NULL,
                'created_at' => '2019-10-11 12:55:59',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            20 => 
            array (
                'id' => 27,
                'parent_id' => 38,
                'order' => 15,
                'title' => '类型管理',
                'icon' => 'fa-bars',
                'uri' => NULL,
                'permission' => NULL,
                'created_at' => '2019-10-12 20:57:25',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            21 => 
            array (
                'id' => 28,
                'parent_id' => 38,
                'order' => 20,
                'title' => '表单数据',
                'icon' => 'fa-bars',
                'uri' => '/form_data',
                'permission' => NULL,
                'created_at' => '2019-10-14 20:23:56',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            22 => 
            array (
                'id' => 29,
                'parent_id' => 38,
                'order' => 21,
                'title' => '消费管理',
                'icon' => 'fa-bars',
                'uri' => '/spend_data',
                'permission' => NULL,
                'created_at' => '2019-10-16 23:55:44',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            23 => 
            array (
                'id' => 30,
                'parent_id' => 17,
                'order' => 13,
                'title' => '消费数据',
                'icon' => 'fa-bars',
                'uri' => '/bill_account_data',
                'permission' => NULL,
                'created_at' => '2019-10-18 17:55:23',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            24 => 
            array (
                'id' => 31,
                'parent_id' => 38,
                'order' => 22,
                'title' => 'crm抓取记录',
                'icon' => 'fa-bars',
                'uri' => '/crm_grab_logs',
                'permission' => NULL,
                'created_at' => '2019-10-22 18:30:51',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            25 => 
            array (
                'id' => 32,
                'parent_id' => 38,
                'order' => 23,
                'title' => '数据导出日志',
                'icon' => 'fa-bars',
                'uri' => '/export_data_logs',
                'permission' => NULL,
                'created_at' => '2019-10-22 20:26:30',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            26 => 
            array (
                'id' => 34,
                'parent_id' => 27,
                'order' => 16,
                'title' => '账户数据',
                'icon' => 'fa-bars',
                'uri' => '/account_data',
                'permission' => NULL,
                'created_at' => '2019-10-29 10:15:27',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            27 => 
            array (
                'id' => 35,
                'parent_id' => 0,
                'order' => 30,
                'title' => '微博人员',
                'icon' => 'fa-bars',
                'uri' => '/weibo_user',
                'permission' => NULL,
                'created_at' => '2019-10-29 16:22:50',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            28 => 
            array (
                'id' => 36,
                'parent_id' => 0,
                'order' => 31,
                'title' => '微博推送表单',
                'icon' => 'fa-bars',
                'uri' => '/weibo_form_data',
                'permission' => NULL,
                'created_at' => '2019-10-29 16:27:12',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            29 => 
            array (
                'id' => 37,
                'parent_id' => 0,
                'order' => 2,
                'title' => '乱七杂八的数据',
                'icon' => 'fa-bars',
                'uri' => NULL,
                'permission' => NULL,
                'created_at' => '2019-10-30 11:00:20',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            30 => 
            array (
                'id' => 38,
                'parent_id' => 0,
                'order' => 14,
                'title' => '导出数据管理',
                'icon' => 'fa-bars',
                'uri' => NULL,
                'permission' => NULL,
                'created_at' => '2019-10-30 11:04:00',
                'updated_at' => '2019-10-30 11:06:00',
            ),
            31 => 
            array (
                'id' => 39,
                'parent_id' => 0,
                'order' => 0,
                'title' => '微博分配规则',
                'icon' => 'fa-bars',
                'uri' => '/weibo_user/settings',
                'permission' => NULL,
                'created_at' => '2019-11-04 21:34:32',
                'updated_at' => '2019-11-04 21:34:32',
            ),
        ));
        
        
    }
}