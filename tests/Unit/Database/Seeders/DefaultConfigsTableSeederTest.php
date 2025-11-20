<?php

namespace Tests\Unit\Database\Seeders;

use App\Models\Core\Configs;
use Database\Seeders\DefaultConfigsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultConfigsTableSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 新規インストール時に全設定が投入されることを確認
     */
    public function testSeedAllConfigsOnFreshInstall()
    {
        // 事前確認: Configsテーブルが空であることを確認
        $this->assertEquals(0, Configs::count());

        // Seederを実行
        $seeder = new DefaultConfigsTableSeeder();
        $seeder->run();

        // 全28設定が投入されたことを確認
        $this->assertEquals(28, Configs::count());

        // 各カテゴリの設定が投入されていることを確認
        $this->assertDatabaseHas('configs', [
            'name' => 'base_site_name',
            'category' => 'general',
            'value' => 'Connect-CMS',
        ]);

        $this->assertDatabaseHas('configs', [
            'name' => 'user_register_enable',
            'category' => 'user_register',
            'value' => '0',
            'additional1' => 1,
        ]);

        $this->assertDatabaseHas('configs', [
            'name' => 'use_normal_login_along_with_auth_method',
            'category' => 'auth',
            'value' => 1,
        ]);

        $this->assertDatabaseHas('configs', [
            'name' => 'app_log_scope',
            'category' => 'app_log',
            'value' => 'select',
        ]);

        $this->assertDatabaseHas('configs', [
            'name' => 'fontsizeselect',
            'category' => 'wysiwyg',
            'value' => 0,
        ]);

        $this->assertDatabaseHas('configs', [
            'name' => 'memory_limit_for_image_resize',
            'category' => 'server',
            'value' => '256M',
        ]);
    }

    /**
     * 冪等性の確認: 複数回実行しても設定が重複しない
     */
    public function testSeederIsIdempotent()
    {
        $seeder = new DefaultConfigsTableSeeder();

        // 1回目の実行
        $seeder->run();
        $first_count = Configs::count();
        $this->assertEquals(28, $first_count);

        // 2回目の実行
        $seeder->run();
        $second_count = Configs::count();

        // レコード数が変わらないことを確認（重複投入されていない）
        $this->assertEquals($first_count, $second_count);
        $this->assertEquals(28, $second_count);
    }

    /**
     * 一部設定が既に存在する場合、不足分のみ投入される
     */
    public function testSeedOnlyMissingConfigs()
    {
        // 事前に一部の設定を投入
        Configs::create([
            'name' => 'base_site_name',
            'category' => 'general',
            'value' => 'Custom CMS',
        ]);

        Configs::create([
            'name' => 'user_register_enable',
            'category' => 'user_register',
            'value' => '1',
            'additional1' => 1,
        ]);

        // 事前投入数を確認
        $this->assertEquals(2, Configs::count());

        // Seederを実行
        $seeder = new DefaultConfigsTableSeeder();
        $seeder->run();

        // 全28設定が存在することを確認
        $this->assertEquals(28, Configs::count());

        // 既存設定が上書きされていないことを確認
        $config = Configs::where('name', 'base_site_name')->first();
        $this->assertEquals('Custom CMS', $config->value); // Connect-CMSではなくCustom CMSのまま

        $config = Configs::where('name', 'user_register_enable')->first();
        $this->assertEquals('1', $config->value); // '0'ではなく'1'のまま
    }

    /**
     * カテゴリごとの設定数を確認
     */
    public function testConfigsCountByCategory()
    {
        $seeder = new DefaultConfigsTableSeeder();
        $seeder->run();

        // カテゴリごとの設定数を確認
        $this->assertEquals(15, Configs::where('category', 'general')->count());
        $this->assertEquals(4, Configs::where('category', 'user_register')->count());
        $this->assertEquals(1, Configs::where('category', 'auth')->count());
        $this->assertEquals(5, Configs::where('category', 'app_log')->count());
        $this->assertEquals(2, Configs::where('category', 'wysiwyg')->count());
        $this->assertEquals(1, Configs::where('category', 'server')->count());
    }

    /**
     * additional1フィールドが正しく設定されることを確認
     */
    public function testAdditional1Field()
    {
        $seeder = new DefaultConfigsTableSeeder();
        $seeder->run();

        // additional1 = 1 の設定を確認（ユーザー登録関連）
        $configs_with_additional1 = Configs::where('additional1', 1)->pluck('name')->toArray();

        $expected_configs = [
            'user_register_enable',
            'user_register_after_message',
            'user_register_mail_subject',
            'user_register_mail_format',
        ];

        sort($configs_with_additional1);
        sort($expected_configs);

        $this->assertEquals($expected_configs, $configs_with_additional1);
    }

    /**
     * NULL値が正しく設定されることを確認
     */
    public function testNullValues()
    {
        $seeder = new DefaultConfigsTableSeeder();
        $seeder->run();

        // valueがnullの設定を確認
        $null_configs = [
            'base_background_color',
            'base_header_color',
            'base_theme',
        ];

        foreach ($null_configs as $config_name) {
            $config = Configs::where('name', $config_name)->first();
            $this->assertNull($config->value);
            $this->assertNull($config->additional1);
        }
    }

    /**
     * Enumの値が正しく設定されることを確認
     */
    public function testEnumValues()
    {
        $seeder = new DefaultConfigsTableSeeder();
        $seeder->run();

        // SmartphoneMenuTemplateType
        $config = Configs::where('name', 'smartphone_menu_template')->first();
        $this->assertNotNull($config->value);

        // BaseHeaderFontColorClass
        $config = Configs::where('name', 'base_header_font_color_class')->first();
        $this->assertNotNull($config->value);

        // ResizedImageSize
        $config = Configs::where('name', 'resized_image_size_initial')->first();
        $this->assertNotNull($config->value);
    }

    /**
     * マイグレーションで設定が作成された後でも正常動作することを確認
     * （Issue #2293の回帰テスト）
     */
    public function testSeederWorksAfterMigrationCreatesConfigs()
    {
        // マイグレーションで1件の設定が作成されたと仮定
        Configs::create([
            'name' => 'mail_auth_method',
            'category' => 'mail',
            'value' => 'smtp',
        ]);

        // Configsテーブルにレコードが存在する状態
        $this->assertEquals(1, Configs::count());

        // Seederを実行
        $seeder = new DefaultConfigsTableSeeder();
        $seeder->run();

        // 基本設定が正しく投入されることを確認（スキップされない）
        $this->assertEquals(29, Configs::count()); // 28 + マイグレーション分1

        // 重要な基本設定が存在することを確認
        $this->assertDatabaseHas('configs', ['name' => 'base_site_name']);
        $this->assertDatabaseHas('configs', ['name' => 'base_header_color']);
        $this->assertDatabaseHas('configs', ['name' => 'app_log_scope']);
    }
}
