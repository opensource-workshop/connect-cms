<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ブログテーブル用のFactory
 */
class Nc3BlogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3Blog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'name' => $this->faker->words(3, true),
            'is_active' => 1,
            'is_latest' => 1,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * アクティブなブログを生成する
     *
     * @return static
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => 1,
                'is_latest' => 1,
            ];
        });
    }

    /**
     * 非アクティブなブログを生成する
     *
     * @return static
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => 0,
                'is_latest' => 1,
            ];
        });
    }

    /**
     * 特定のキーを持つブログを生成する
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