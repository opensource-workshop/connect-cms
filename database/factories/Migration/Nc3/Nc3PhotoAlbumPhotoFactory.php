<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3PhotoAlbumPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3フォトアルバム写真テーブル用のFactory
 */
class Nc3PhotoAlbumPhotoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3PhotoAlbumPhoto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_key' => $this->faker->uuid(),
            'key' => $this->faker->uuid(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->text(100),
            'language_id' => 2, // Japanese
            'status' => 1, // published
            'block_id' => 1,
            'is_latest' => 1,
            'is_active' => 1,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * 特定のアルバムに紐づく写真
     *
     * @param string $albumKey
     * @return static
     */
    public function forAlbum(string $albumKey): static
    {
        return $this->state(function (array $attributes) use ($albumKey) {
            return [
                'album_key' => $albumKey,
            ];
        });
    }

    /**
     * キーを指定して写真を作成
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
     * タイトルを指定して写真を作成
     *
     * @param string $title
     * @return static
     */
    public function withTitle(string $title): static
    {
        return $this->state(function (array $attributes) use ($title) {
            return [
                'title' => $title,
            ];
        });
    }

    /**
     * 特定のブロックに紐づく写真
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
}