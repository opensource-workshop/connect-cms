<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3PhotoAlbum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3フォトアルバムテーブル用のFactory
 */
class Nc3PhotoAlbumFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3PhotoAlbum::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'block_id' => 1,
            'key' => $this->faker->uuid(),
            'name' => $this->faker->words(3, true) . 'アルバム',
            'description' => $this->faker->text(100),
            'language_id' => 2, // Japanese
            'status' => 1, // published
            'is_latest' => 1,
            'is_active' => 1,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * 特定のブロックに紐づくアルバム
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
     * キーを指定してアルバムを作成
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

    /**
     * 名前を指定してアルバムを作成
     *
     * @param string $name
     * @return static
     */
    public function withName(string $name): static
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name' => $name,
            ];
        });
    }
}