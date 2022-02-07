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
                    [
                        'plugin_name' => 'Bbses',
                        'plugin_name_full' => '掲示板',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Cabinets',
                        'plugin_name_full' => 'キャビネット',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Calendars',
                        'plugin_name_full' => 'カレンダー',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Counters',
                        'plugin_name_full' => 'カウンター',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Faqs',
                        'plugin_name_full' => 'FAQ',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Linklists',
                        'plugin_name_full' => 'リンクリスト',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Searchs',
                        'plugin_name_full' => 'サイト内検索',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Tabs',
                        'plugin_name_full' => 'タブ',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Slideshows',
                        'plugin_name_full' => 'スライドショー',
                        'display_flag' => 1,
                        'display_sequence' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'plugin_name' => 'Photoalbums',
                        'plugin_name_full' => 'フォトアルバム',
                        'display_flag' => 1,
                        'display_sequence' => 0,
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
