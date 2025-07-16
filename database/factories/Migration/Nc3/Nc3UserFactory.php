<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ユーザーテーブル用のFactory
 */
class Nc3UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->userName(),
            'email' => $this->faker->email(),
            'password' => '$2y$10$' . $this->faker->regexify('[A-Za-z0-9./]{53}'),
            'handlename' => $this->faker->name(),
            'status' => 1,
            'activate_key' => '',
            'role_key' => 'common_user',
            'created' => $this->faker->dateTime(),
            'modified' => $this->faker->dateTime(),
            'created_user' => 1,
            'modified_user' => 1,
        ];
    }

    /**
     * システム管理者ユーザーを生成する
     *
     * @return static
     */
    public function systemAdmin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'handlename' => 'システム管理者',
                'status' => 1,
                'role_key' => 'system_administrator',
            ];
        });
    }

    /**
     * サイト管理者ユーザーを生成する
     *
     * @return static
     */
    public function siteAdmin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'username' => 'site_admin',
                'email' => 'site@example.com',
                'handlename' => 'サイト管理者',
                'status' => 1,
                'role_key' => 'administrator',
            ];
        });
    }

    /**
     * 一般ユーザーを生成する
     *
     * @return static
     */
    public function generalUser(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'username' => 'user' . $this->faker->numberBetween(1, 100),
                'email' => 'user' . $this->faker->numberBetween(1, 100) . '@example.com',
                'handlename' => 'ユーザー' . $this->faker->numberBetween(1, 100),
                'status' => 1,
                'role_key' => 'common_user',
            ];
        });
    }

    /**
     * 非アクティブユーザーを生成する
     *
     * @return static
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 0,
            ];
        });
    }

    /**
     * テスト用メールアドレスのユーザーを生成する
     *
     * @return static
     */
    public function testEmail(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'test＠example.com', // 全角@
            ];
        });
    }
}