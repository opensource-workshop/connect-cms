<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3サイト設定テーブル用のFactory
 */
class Nc3SiteSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3SiteSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->word(),
            'value' => $this->faker->sentence(),
            'label' => $this->faker->word(),
            'language_id' => 2, // デフォルトは日本語
            'created' => $this->faker->dateTime(),
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * サイト名の設定を生成する
     *
     * @return static
     */
    public function siteName(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'Site.name',
                'value' => $this->faker->company(),
                'label' => 'サイト名',
                'language_id' => 2,
            ];
        });
    }

    /**
     * サイトキャッチコピーの設定を生成する
     *
     * @return static
     */
    public function siteCatchcopy(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'Site.catchcopy',
                'value' => $this->faker->sentence(),
                'label' => 'キャッチコピー',
                'language_id' => 2,
            ];
        });
    }

    /**
     * サイト説明の設定を生成する
     *
     * @return static
     */
    public function siteDescription(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'Site.description',
                'value' => $this->faker->paragraph(),
                'label' => 'サイト説明',
                'language_id' => 2,
            ];
        });
    }

    /**
     * サイトメタキーワードの設定を生成する
     *
     * @return static
     */
    public function siteMetaKeywords(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'Site.meta_keywords',
                'value' => implode(',', $this->faker->words(5)),
                'label' => 'メタキーワード',
                'language_id' => 2,
            ];
        });
    }

    /**
     * サイトメタ説明の設定を生成する
     *
     * @return static
     */
    public function siteMetaDescription(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'Site.meta_description',
                'value' => $this->faker->sentence(),
                'label' => 'メタ説明',
                'language_id' => 2,
            ];
        });
    }

    /**
     * アプリケーションサイト名の設定を生成する（nc3ExportBasic用）
     *
     * @return static
     */
    public function appSiteName(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'App.site_name',
                'value' => $this->faker->company(),
                'label' => 'アプリケーションサイト名',
                'language_id' => 2,
            ];
        });
    }

    /**
     * メタ説明の設定を生成する（nc3ExportBasic用）
     *
     * @return static
     */
    public function metaDescription(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'Meta.description',
                'value' => $this->faker->paragraph(),
                'label' => 'メタ説明',
                'language_id' => 2,
            ];
        });
    }
}