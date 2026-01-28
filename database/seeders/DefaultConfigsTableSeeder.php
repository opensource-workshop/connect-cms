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
     * デフォルト設定の定義を取得
     * additional1: 1 = ユーザー登録機能で使用される設定項目
     *
     * @return array
     */
    private function getDefaultConfigs()
    {
        return [
            'general' => [
                'base_background_color' => ['value' => null],
                'base_header_color' => ['value' => null],
                'base_site_name' => ['value' => 'Connect-CMS'],
                'base_header_hidden' => ['value' => '0'],
                'base_header_fix' => ['value' => '0'],
                'base_mousedown_off' => ['value' => '0'],
                'base_contextmenu_off' => ['value' => '0'],
                'base_touch_callout' => ['value' => '0'],
                'base_header_login_link' => ['value' => '1'],
                'base_theme' => ['value' => null],
                'base_layout' => ['value' => config('connect.BASE_LAYOUT_DEFAULT')],
                'smartphone_menu_template' => ['value' => SmartphoneMenuTemplateType::opencurrenttree],
                'base_login_password_reset' => ['value' => 1],
                'use_mypage' => ['value' => 1],
                'base_login_redirect_previous_page' => ['value' => 0],
                'base_header_font_color_class' => ['value' => BaseHeaderFontColorClass::navbar_dark],
            ],
            'user_register' => [
                'user_register_enable' => ['value' => '0', 'additional1' => 1],
                'user_register_after_message' => [
                    'value' => 'ユーザ登録が完了しました。登録したログインID、パスワードでログインしてください。',
                    'additional1' => 1,
                ],
                'user_register_mail_subject' => [
                    'value' => 'ユーザ登録が完了しました - [[site_name]]',
                    'additional1' => 1,
                ],
                'user_register_mail_format' => [
                    'value' => '--- 登録内容

[[body]]


ユーザ登録が完了しました。登録したログインID、パスワードでログインしてください。',
                    'additional1' => 1,
                ],
            ],
            'auth' => [
                'use_normal_login_along_with_auth_method' => ['value' => 1],
            ],
            'app_log' => [
                'app_log_scope' => ['value' => 'select'],
                'save_log_type_login' => ['value' => '1'],
                'save_log_type_sendmail' => ['value' => '1'],
                'save_log_type_password' => ['value' => '1'],
                'save_log_type_register' => ['value' => '1'],
            ],
            'wysiwyg' => [
                'fontsizeselect' => ['value' => 0],
                'resized_image_size_initial' => ['value' => ResizedImageSize::getDefault()],
            ],
            'server' => [
                'memory_limit_for_image_resize' => ['value' => '256M'],
            ],
        ];
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 既存の設定名を一括取得（パフォーマンス最適化: N回のクエリ → 1回のクエリ）
        $existing_config_names = Configs::pluck('name')->toArray();

        // カテゴリごとに設定を投入（冪等性を確保）
        foreach ($this->getDefaultConfigs() as $category => $configs) {
            foreach ($configs as $name => $data) {
                // 既存設定が存在しない場合のみ投入
                if (!in_array($name, $existing_config_names)) {
                    Configs::create([
                        'name' => $name,
                        'category' => $category,
                        'value' => $data['value'],
                        'additional1' => $data['additional1'] ?? null,
                    ]);
                }
            }
        }
    }
}
