<?php

use Illuminate\Database\Seeder;

class DefaultPluginsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('plugins')->count() == 0) {
            DB::table('plugins')->insert(
                [
                    /* -------------------------
                       Display plugin 
                       ------------------------- */
                    [
                        'plugin_name'=>'Blogs',
                        'plugin_name_full'=>'ブログ',
                        'display_flag'=>1,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name'=>'Contents',
                        'plugin_name_full'=>'固定記事',
                        'display_flag'=>1,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name'=>'Forms',
                        'plugin_name_full'=>'フォーム',
                        'display_flag'=>1,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name'=>'Menus',
                        'plugin_name_full'=>'メニュー',
                        'display_flag'=>1,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name'=>'Databases',
                        'plugin_name_full'=>'データベース',
                        'display_flag'=>1,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name'=>'Reservations',
                        'plugin_name_full'=>'施設予約',
                        'display_flag'=>1,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name'=>'Whatsnews',
                        'plugin_name_full'=>'新着情報',
                        'display_flag'=>1,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    /* -------------------------
                       Hidden plugin
                       ------------------------- */
                    [
                        'plugin_name'=>'Opacs',
                        'plugin_name_full'=>'OPAC',
                        'display_flag'=>0,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name'=>'Openingcalendars',
                        'plugin_name_full'=>'開館カレンダー',
                        'display_flag'=>0,
                        'display_sequence'=>0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                ]
            );
        }
    }
}
