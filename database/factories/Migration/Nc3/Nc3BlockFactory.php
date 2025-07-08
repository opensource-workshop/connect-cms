<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Block;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ブロックテーブル用のFactory
 */
class Nc3BlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3Block::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'room_id' => 1,
            'plugin_key' => 'pages',
            'created' => $this->faker->dateTime(),
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * 特定のルーム用のブロックを生成する
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
     * 特定のプラグイン用のブロックを生成する
     *
     * @param string $pluginKey
     * @return static
     */
    public function forPlugin(string $pluginKey): static
    {
        return $this->state(function (array $attributes) use ($pluginKey) {
            return [
                'plugin_key' => $pluginKey,
            ];
        });
    }

    /**
     * カレンダープラグイン用のブロック
     *
     * @return static
     */
    public function calendarPlugin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plugin_key' => 'calendars',
            ];
        });
    }

    /**
     * ブログプラグイン用のブロック
     *
     * @return static
     */
    public function blogPlugin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plugin_key' => 'blogs',
            ];
        });
    }

    /**
     * 掲示板プラグイン用のブロック
     *
     * @return static
     */
    public function bbsPlugin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plugin_key' => 'bbses',
            ];
        });
    }

    /**
     * 予約プラグイン用のブロック
     *
     * @return static
     */
    public function reservationPlugin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plugin_key' => 'reservations',
            ];
        });
    }

    /**
     * フォトアルバムプラグイン用のブロック
     *
     * @return static
     */
    public function photoAlbumPlugin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plugin_key' => 'photo_albums',
            ];
        });
    }

    /**
     * 動画プラグイン用のブロック
     *
     * @return static
     */
    public function videoPlugin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plugin_key' => 'videos',
            ];
        });
    }

    /**
     * 特定のキーを持つブロック
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