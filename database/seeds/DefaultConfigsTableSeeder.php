<?php

use Illuminate\Database\Seeder;

use App\Models\Core\Configs;

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
                        'name'=>'base_header_hidden',
                        'value'=>'0',
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_fix',
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

        if (DB::table('configs')->where('name', 'base_login_password_reset')->count() == 0) {
            // パスワードリセットの使用
            $configs = Configs::create([
                'name' => 'base_login_password_reset',
                'category' => 'general',
                'value' => 1
            ]);
        }

        if (DB::table('configs')->where('name', 'use_mypage')->count() == 0) {
            // マイページの使用
            $configs = Configs::create([
                'name' => 'use_mypage',
                'category' => 'general',
                'value' => 1
            ]);
        }

        if (DB::table('configs')->where('name', 'use_normal_login_along_with_auth_method')->count() == 0) {
            // 外部認証と併せて、通常ログインも使用
            $configs = Configs::create([
                'name' => 'use_normal_login_along_with_auth_method',
                'category' => 'auth',
                'value' => 1
            ]);
        }

        if (DB::table('configs')->where('name', 'base_login_redirect_previous_page')->count() == 0) {
            // ログイン時に元いたページに遷移 設定
            $configs = Configs::create([
                'name' => 'base_login_redirect_previous_page',
                'category' => 'general',
                'value' => 0
            ]);
        }

    }
}
