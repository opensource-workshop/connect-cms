<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3RoomLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ルーム多言語情報テーブル用のFactory
 */
class Nc3RoomLanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3RoomLanguage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => $this->faker->numberBetween(1, 100),
            'language_id' => 2, // 日本語
            'name' => $this->faker->words(2, true),
            'created' => $this->faker->dateTime(),
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * 特定のルーム用の多言語情報を生成する
     *
     * @param int $roomId
     * @return static
     */
    public function forRoom(int $roomId): static
    {
        return $this->state(function (array $attributes) use ($roomId) {
            return [
                'room_id' => $roomId,
            ];
        });
    }

    /**
     * 日本語情報を生成する
     *
     * @return static
     */
    public function japanese(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'language_id' => 2,
                'name' => 'サンプルルーム',
            ];
        });
    }

    /**
     * 英語情報を生成する
     *
     * @return static
     */
    public function english(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'language_id' => 1,
                'name' => 'Sample Room',
            ];
        });
    }
}