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
        // 基本設定の投入（個別チェックで冪等性を確保）
        if (Configs::where('name', 'base_background_color')->count() == 0) {
            Configs::create([
                'name' => 'base_background_color',
                'category' => 'general',
                'value' => null,
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'base_header_color')->count() == 0) {
            Configs::create([
                'name' => 'base_header_color',
                'category' => 'general',
                'value' => null,
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'base_site_name')->count() == 0) {
            Configs::create([
                'name' => 'base_site_name',
                'category' => 'general',
                'value' => 'Connect-CMS',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'base_header_hidden')->count() == 0) {
            Configs::create([
                'name' => 'base_header_hidden',
                'category' => 'general',
                'value' => '0',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'base_header_fix')->count() == 0) {
            Configs::create([
                'name' => 'base_header_fix',
                'category' => 'general',
                'value' => '0',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'base_mousedown_off')->count() == 0) {
            Configs::create([
                'name' => 'base_mousedown_off',
                'category' => 'general',
                'value' => '0',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'base_contextmenu_off')->count() == 0) {
            Configs::create([
                'name' => 'base_contextmenu_off',
                'category' => 'general',
                'value' => '0',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'base_touch_callout')->count() == 0) {
            Configs::create([
                'name' => 'base_touch_callout',
                'category' => 'general',
                'value' => '0',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'base_header_login_link')->count() == 0) {
            Configs::create([
                'name' => 'base_header_login_link',
                'category' => 'general',
                'value' => '1',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'user_register_enable')->count() == 0) {
            Configs::create([
                'name' => 'user_register_enable',
                'category' => 'user_register',
                'value' => '0',
                'additional1' => 1,
            ]);
        }

        if (Configs::where('name', 'base_theme')->count() == 0) {
            Configs::create([
                'name' => 'base_theme',
                'category' => 'general',
                'value' => null,
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'smartphone_menu_template')->count() == 0) {
            Configs::create([
                'name' => 'smartphone_menu_template',
                'category' => 'general',
                'value' => SmartphoneMenuTemplateType::opencurrenttree,
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'app_log_scope')->count() == 0) {
            // 取得するログの初期値の設定
            Configs::create([
                'name' => 'app_log_scope',
                'category' => 'app_log',
                'value' => 'select',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'save_log_type_login')->count() == 0) {
            Configs::create([
                'name' => 'save_log_type_login',
                'category' => 'app_log',
                'value' => '1',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'save_log_type_sendmail')->count() == 0) {
            Configs::create([
                'name' => 'save_log_type_sendmail',
                'category' => 'app_log',
                'value' => '1',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'save_log_type_password')->count() == 0) {
            Configs::create([
                'name' => 'save_log_type_password',
                'category' => 'app_log',
                'value' => '1',
                'additional1' => null,
            ]);
        }

        if (Configs::where('name', 'save_log_type_register')->count() == 0) {
            Configs::create([
                'name' => 'save_log_type_register',
                'category' => 'app_log',
                'value' => '1',
                'additional1' => null,
            ]);
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
