<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3BlogFrameSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ブログフレーム設定テーブル用のFactory
 */
class Nc3BlogFrameSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3BlogFrameSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'frame_key' => $this->faker->uuid(),
            'content_key' => $this->faker->uuid(),
            'articles_per_page' => 10,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * 特定のフレームキーを持つ設定を生成する
     *
     * @param string $frameKey
     * @return static
     */
    public function forFrame(string $frameKey): static
    {
        return $this->state(function (array $attributes) use ($frameKey) {
            return [
                'frame_key' => $frameKey,
            ];
        });
    }

    /**
     * 特定のコンテンツキーを持つ設定を生成する
     *
     * @param string $contentKey
     * @return static
     */
    public function forContent(string $contentKey): static
    {
        return $this->state(function (array $attributes) use ($contentKey) {
            return [
                'content_key' => $contentKey,
            ];
        });
    }

    /**
     * 特定の表示件数を持つ設定を生成する
     *
     * @param int $articlesPerPage
     * @return static
     */
    public function withArticlesPerPage(int $articlesPerPage): static
    {
        return $this->state(function (array $attributes) use ($articlesPerPage) {
            return [
                'articles_per_page' => $articlesPerPage,
            ];
        });
    }
}