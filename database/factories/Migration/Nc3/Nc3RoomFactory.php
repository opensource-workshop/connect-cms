<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ルームテーブル用のFactory
 */
class Nc3RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3Room::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'space_id' => 2, // COMMUNITY_SPACE
            'page_id_top' => $this->faker->numberBetween(1, 100),
            'default_participation' => 0,
            'need_approval' => 0,
            'default_role_key' => 'general_user',
            'sort_key' => $this->faker->lexify('??????'),
            'created' => $this->faker->dateTime(),
            'modified' => $this->faker->dateTime(),
            'created_user' => 1,
            'modified_user' => 1,
        ];
    }

    /**
     * パブリックスペースのルームを生成する
     *
     * @return static
     */
    public function publicSpace(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'space_id' => 2, // PUBLIC_SPACE
            ];
        });
    }

    /**
     * コミュニティスペースのルームを生成する
     *
     * @return static
     */
    public function communitySpace(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'space_id' => 4, // COMMUNITY_SPACE
            ];
        });
    }

    /**
     * プライベートスペースのルームを生成する（エクスポート対象外）
     *
     * @return static
     */
    public function privateSpace(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'space_id' => 3, // PRIVATE_SPACE
            ];
        });
    }

    /**
     * デフォルト参加のルームを生成する
     *
     * @return static
     */
    public function defaultParticipation(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'default_participation' => 1,
            ];
        });
    }

    /**
     * 古いNC3バージョン用のルーム（sort_keyなし）を生成する
     *
     * @return static
     */
    public function oldVersion(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'sort_key' => null,
            ];
        });
    }
}