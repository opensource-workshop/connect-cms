<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Space;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3スペーステーブル用のFactory
 */
class Nc3SpaceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3Space::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'lft' => null,
            'rght' => null,
            'type' => 1,
            'plugin_key' => null,
            'default_setting_action' => null,
            'room_disk_size' => null,
            'room_id_root' => 1,
            'page_id_top' => null,
            'permalink' => $this->faker->slug(),
            'is_m17n' => 0,
            'after_user_save_model' => null,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * パブリックスペース (ID=2)
     *
     * @return static
     */
    public function publicSpace(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 2,
                'type' => 1,
                'permalink' => 'public',
            ];
        });
    }

    /**
     * プライベートスペース (ID=3)
     *
     * @return static
     */
    public function privateSpace(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 3,
                'type' => 2,
                'permalink' => 'private',
            ];
        });
    }

    /**
     * コミュニティスペース (ID=4)
     *
     * @return static
     */
    public function communitySpace(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => 4,
                'type' => 3,
                'permalink' => 'community',
            ];
        });
    }

    /**
     * 特定のルートルームIDを設定
     *
     * @param int $roomId
     * @return static
     */
    public function withRootRoom(int $roomId): static
    {
        return $this->state(function (array $attributes) use ($roomId) {
            return [
                'room_id_root' => $roomId,
            ];
        });
    }
}