<?php

namespace Database\Factories\Migration;

use App\Models\Migration\MigrationMapping;
use Illuminate\Database\Eloquent\Factories\Factory;

class MigrationMappingFactory extends Factory
{
    /**
     * Factoryが対応するモデルのクラス名
     *
     * @var string
     */
    protected $model = MigrationMapping::class;

    /**
     * モデルのデフォルト状態を定義
     *
     * @return array
     */
    public function definition()
    {
        return [
            'target_source_table' => $this->faker->randomElement([
                'source_pages',
                'source_pages_lang',
                'blogs',
                'blogs_post',
                'blogs_post_from_key',
                'bbses',
                'bbses_post',
                'bbses_post_from_key',
                'databases',
                'databases_post',
                'databases_post_from_key',
                'faqs',
                'linklists',
                'cabinets',
                'calendars',
                'photoalbums',
            ]),
            'source_key' => $this->faker->randomNumber(4),
            'destination_key' => $this->faker->randomNumber(4),
            'note' => $this->faker->optional()->randomElement([
                json_encode(['test' => 'data']),
                json_encode(['example' => 'value', 'number' => $this->faker->randomNumber()]),
                null
            ]),
        ];
    }

    /**
     * 通常ページのマッピングを作成
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function sourcePages()
    {
        return $this->state(function (array $attributes) {
            return [
                'target_source_table' => 'source_pages',
                'source_key' => $this->faker->randomNumber(3),
                'destination_key' => sprintf('%04d', $this->faker->numberBetween(1, 9999)),
                'note' => null,
            ];
        });
    }

    /**
     * 多言語ページのマッピングを作成
     * noteフィールドに言語情報のJSONを含む
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function sourcePagesLang()
    {
        return $this->state(function (array $attributes) {
            $pageId = $this->faker->randomNumber(3);
            $languageId = $this->faker->randomElement([1, 2]); // 1=英語, 2=日本語
            
            return [
                'target_source_table' => 'source_pages_lang',
                'source_key' => $pageId . '_' . $languageId,
                'destination_key' => sprintf('%04d', $this->faker->numberBetween(1, 9999)),
                'note' => json_encode([
                    'language_id' => $languageId,
                    'language_code' => $languageId == 2 ? 'ja' : 'en',
                    'room_id' => $this->faker->randomNumber(2),
                    'room_page_id_top' => $this->faker->optional()->randomNumber(3),
                    'nc3_page_id' => $pageId,
                ]),
            ];
        });
    }

    /**
     * ブログ投稿のマッピングを作成
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function blogPost()
    {
        return $this->state(function (array $attributes) {
            return [
                'target_source_table' => 'blogs_post',
                'source_key' => $this->faker->randomNumber(4),
                'destination_key' => $this->faker->randomNumber(4),
                'note' => null,
            ];
        });
    }

    /**
     * ブログ投稿キー参照のマッピングを作成
     * source_keyにUUID形式の文字列を使用
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function blogPostFromKey()
    {
        return $this->state(function (array $attributes) {
            return [
                'target_source_table' => 'blogs_post_from_key',
                'source_key' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}'),
                'destination_key' => $this->faker->randomNumber(4),
                'note' => null,
            ];
        });
    }

    /**
     * 掲示板投稿のマッピングを作成
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function bbsPost()
    {
        return $this->state(function (array $attributes) {
            return [
                'target_source_table' => 'bbses_post',
                'source_key' => $this->faker->randomNumber(4),
                'destination_key' => $this->faker->randomNumber(4),
                'note' => null,
            ];
        });
    }

    /**
     * データベース投稿のマッピングを作成
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function databasePost()
    {
        return $this->state(function (array $attributes) {
            return [
                'target_source_table' => 'databases_post',
                'source_key' => $this->faker->randomNumber(4),
                'destination_key' => $this->faker->randomNumber(4),
                'note' => null,
            ];
        });
    }
}