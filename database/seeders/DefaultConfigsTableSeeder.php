<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Core\Configs;

use App\Enums\BaseHeaderFontColorClass;
use App\Enums\ResizedImageSize;
use App\Enums\SmartphoneMenuTemplateType;

class DefaultConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Configs::count() == 0) {
            Configs::insert(
                [
                    [
                        'name'=>'base_background_color',
                        'value'=>null,
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_color',
                        'value'=>null,
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_site_name',
                        'value'=>'Connect-CMS',
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_hidden',
                        'value'=>'0',
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_fix',
                        'value'=>'0',
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_mousedown_off',
                        'value'=>'0',
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_contextmenu_off',
                        'value'=>'0',
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_touch_callout',
                        'value'=>'0',
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_header_login_link',
                        'value'=>'1',
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'user_register_enable',
                        'value'=>'0',
                        'additional1' => 1,
                        'category'=>'user_register',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'base_theme',
                        'value'=>null,
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name'=>'smartphone_menu_template',
                        'value'=>SmartphoneMenuTemplateType::opencurrenttree,
                        'additional1' => null,
                        'category'=>'general',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name' => 'app_log_scope',    // これ以降は取得するログの初期値の設定
                        'value' => 'select',
                        'additional1' => null,
                        'category' => 'app_log',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name' => 'save_log_type_login',
                        'value' => '1',
                        'additional1' => null,
                        'category' => 'app_log',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name' => 'save_log_type_sendmail',
                        'value' => '1',
                        'additional1' => null,
                        'category' => 'app_log',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name' => 'save_log_type_password',
                        'value' => '1',
                        'additional1' => null,
                        'category' => 'app_log',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'name' => 'save_log_type_register',
                        'value' => '1',
                        'additional1' => null,
                        'category' => 'app_log',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]
                ]
            );
        }

        if (Configs::where('name', 'base_login_password_reset')->count() == 0) {
            // パスワードリセットの使用
            Configs::create([
                'name' => 'base_login_password_reset',
                'category' => 'general',
                'value' => 1
            ]);
        }

        if (Configs::where('name', 'use_mypage')->count() == 0) {
            // マイページの使用
            Configs::create([
                'name' => 'use_mypage',
                'category' => 'general',
                'value' => 1
            ]);
        }

        if (Configs::where('name', 'use_normal_login_along_with_auth_method')->count() == 0) {
            // 外部認証と併せて、通常ログインも使用
            Configs::create([
                'name' => 'use_normal_login_along_with_auth_method',
                'category' => 'auth',
                'value' => 1
            ]);
        }

        if (Configs::where('name', 'base_login_redirect_previous_page')->count() == 0) {
            // ログイン時に元いたページに遷移 設定
            Configs::create([
                'name' => 'base_login_redirect_previous_page',
                'category' => 'general',
                'value' => 0
            ]);
        }

        if (Configs::where('name', 'user_register_after_message')->count() == 0) {
            // 本登録後のメッセージ
            Configs::create([
                'name' => 'user_register_after_message',
                'category' => 'user_register',
                'value' => 'ユーザ登録が完了しました。登録したログインID、パスワードでログインしてください。',
                'additional1' => 1,
            ]);
        }

        if (Configs::where('name', 'base_header_font_color_class')->count() == 0) {
            // 画面の基本のヘッダー文字色
            Configs::create([
                'name' => 'base_header_font_color_class',
                'category' => 'general',
                'value' => BaseHeaderFontColorClass::navbar_dark
            ]);
        }

        if (Configs::where('name', 'fontsizeselect')->count() == 0) {
            // wysiwygで文字サイズの使用
            Configs::create([
                'name' => 'fontsizeselect',
                'category' => 'wysiwyg',
                'value' => 0
            ]);
        }

        if (Configs::where('name', 'memory_limit_for_image_resize')->count() == 0) {
            // 画像リサイズ時のPHPメモリ数
            Configs::create([
                'name' => 'memory_limit_for_image_resize',
                'category' => 'server',
                'value' => '256M'
            ]);
        }

        if (Configs::where('name', 'resized_image_size_initial')->count() == 0) {
            // 初期に選択させる画像サイズ
            Configs::create([
                'name' => 'resized_image_size_initial',
                'category' => 'wysiwyg',
                'value' => ResizedImageSize::getDefault(),
            ]);
        }

        if (Configs::where('name', 'user_register_mail_subject')->count() == 0) {
            // 本登録メール件名
            Configs::create([
                'name' => 'user_register_mail_subject',
                'category' => 'user_register',
                'value' => 'ユーザ登録が完了しました - [[site_name]]',
                'additional1' => 1,
            ]);
        }

        if (Configs::where('name', 'user_register_mail_format')->count() == 0) {
            // 本登録メールフォーマット
            Configs::create([
                'name' => 'user_register_mail_format',
                'category' => 'user_register',
                'value' => '--- 登録内容

[[body]]


ユーザ登録が完了しました。登録したログインID、パスワードでログインしてください。',
                'additional1' => 1,
            ]);
        }

    }
}
