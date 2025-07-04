<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3RoleRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ルーム権限テーブル用のFactory
 */
class Nc3RoleRoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3RoleRoom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 最小限のフィールドのみ設定
            'id' => $this->faker->unique()->numberBetween(1, 1000),
        ];
    }

    /**
     * ルーム管理者権限を生成する
     *
     * @return static
     */
    public function roomAdministrator(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 1,
            ];
        });
    }

    /**
     * チーフエディター権限を生成する
     *
     * @return static
     */
    public function chiefEditor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 2,
            ];
        });
    }

    /**
     * エディター権限を生成する
     *
     * @return static
     */
    public function editor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 3,
            ];
        });
    }

    /**
     * 一般ユーザー権限を生成する
     *
     * @return static
     */
    public function generalUser(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 4,
            ];
        });
    }

    /**
     * 訪問者権限を生成する
     *
     * @return static
     */
    public function visitor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 5,
            ];
        });
    }
}