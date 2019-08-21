<?php

use Illuminate\Database\Seeder;

class DefaultConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('configs')->count() == 0) {
            DB::table('configs')->insert(
                [
                    [
                        'name'=>'base_background_color',
                        'value'=>null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_color',
                        'value'=>null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_site_name',
                        'value'=>'Connect-CMS',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_fix_xs',
                        'value'=>'0',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_fix_sm',
                        'value'=>'0',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_fix_md',
                        'value'=>'0',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_mousedown_off',
                        'value'=>'0',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_contextmenu_off',
                        'value'=>'0',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_touch_callout',
                        'value'=>'0',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_login_link',
                        'value'=>'1',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'user_register_enable',
                        'value'=>'0',
                        'category'=>'user_register',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_theme',
                        'value'=>null,
                        'category'=>'user_register',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                ]
            );
        }

    }
}
