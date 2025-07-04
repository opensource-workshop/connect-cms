<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3RoleRoomsUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ルールユーザー関連テーブル用のFactory
 */
class Nc3RoleRoomsUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3RoleRoomsUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 100),
            'room_id' => $this->faker->numberBetween(1, 100),
            'roles_room_id' => $this->faker->numberBetween(1, 5),
        ];
    }

    /**
     * 特定のユーザー・ルーム用の関連を生成する
     *
     * @param int $userId
     * @param int $roomId
     * @return static
     */
    public function forUserAndRoom(int $userId, int $roomId): static
    {
        return $this->state(function (array $attributes) use ($userId, $roomId) {
            return [
                'user_id' => $userId,
                'room_id' => $roomId,
            ];
        });
    }

    /**
     * ルーム管理者の関連を生成する
     *
     * @return static
     */
    public function roomAdmin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'roles_room_id' => 1, // room_administrator
            ];
        });
    }

    /**
     * チーフエディターの関連を生成する
     *
     * @return static
     */
    public function chiefEditor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'roles_room_id' => 2, // chief_editor
            ];
        });
    }

    /**
     * エディターの関連を生成する
     *
     * @return static
     */
    public function editor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'roles_room_id' => 3, // editor
            ];
        });
    }

    /**
     * 一般ユーザーの関連を生成する
     *
     * @return static
     */
    public function generalUser(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'roles_room_id' => 4, // general_user
            ];
        });
    }

    /**
     * 訪問者の関連を生成する
     *
     * @return static
     */
    public function visitor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'roles_room_id' => 5, // visitor
            ];
        });
    }
}