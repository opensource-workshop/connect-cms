<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3動画テーブル用のFactory
 */
class Nc3VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3Video::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'block_id' => 1,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'is_latest' => 1,
            'language_id' => 2, // 日本語
            'category_id' => 0, // カテゴリなし
            'is_active' => 1, // アクティブ
            'created' => $this->faker->dateTime(),
            'modified' => $this->faker->dateTime(),
            'created_user' => 1,
            'modified_user' => 1,
        ];
    }

    /**
     * 最新版の動画
     *
     * @return static
     */
    public function latest(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_latest' => 1,
            ];
        });
    }

    /**
     * 特定のブロック用の動画
     *
     * @param int $blockId
     * @return static
     */
    public function forBlock(int $blockId): static
    {
        return $this->state(function (array $attributes) use ($blockId) {
            return [
                'block_id' => $blockId,
            ];
        });
    }

    /**
     * 特定のキーを持つ動画
     *
     * @param string $key
     * @return static
     */
    public function withKey(string $key): static
    {
        return $this->state(function (array $attributes) use ($key) {
            return [
                'key' => $key,
            ];
        });
    }
}