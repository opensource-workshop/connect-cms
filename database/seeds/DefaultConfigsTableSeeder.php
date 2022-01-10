<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Core\Configs;

use App\Enums\BaseHeaderFontColorClass;
use App\Enums\ResizedImageSize;

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

        if (DB::table('configs')->where('name', 'user_register_after_message')->count() == 0) {
            // 本登録後のメッセージ
            $configs = Configs::create([
                'name' => 'user_register_after_message',
                'category' => 'user_register',
                'value' => 'ユーザ登録が完了しました。登録したログインID、パスワードでログインしてください。'
            ]);
        }

        if (DB::table('configs')->where('name', 'base_header_font_color_class')->count() == 0) {
            // 画面の基本のヘッダー文字色
            $configs = Configs::create([
                'name' => 'base_header_font_color_class',
                'category' => 'general',
                'value' => BaseHeaderFontColorClass::navbar_dark
            ]);
        }

        if (DB::table('configs')->where('name', 'fontsizeselect')->count() == 0) {
            // wysiwygで文字サイズの使用
            $configs = Configs::create([
                'name' => 'fontsizeselect',
                'category' => 'wysiwyg',
                'value' => 0
            ]);
        }

        if (DB::table('configs')->where('name', 'memory_limit_for_image_resize')->count() == 0) {
            // 画像リサイズ時のPHPメモリ数
            $configs = Configs::create([
                'name' => 'memory_limit_for_image_resize',
                'category' => 'server',
                'value' => '256M'
            ]);
        }

        if (Configs::where('name', 'resized_image_size_initial')->count() == 0) {
            // 初期に選択させる画像サイズ
            $configs = Configs::create([
                'name' => 'resized_image_size_initial',
                'category' => 'wysiwyg',
                'value' => ResizedImageSize::getDefault(),
            ]);
        }

    }
}
