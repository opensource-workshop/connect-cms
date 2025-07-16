<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3言語テーブル用のFactory
 */
class Nc3LanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3Language::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->randomElement(['ja', 'en']),
            'weight' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(80),
            'created' => $this->faker->dateTime(),
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * 日本語言語設定を生成する
     *
     * @return static
     */
    public function japanese(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => Nc3Language::language_id_ja,
                'code' => 'ja',
                'weight' => 1,
                'is_active' => true,
            ];
        });
    }

    /**
     * 英語言語設定を生成する
     *
     * @return static
     */
    public function english(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => Nc3Language::language_id_en,
                'code' => 'en',
                'weight' => 2,
                'is_active' => true,
            ];
        });
    }

    /**
     * アクティブな言語設定を生成する
     *
     * @return static
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * 非アクティブな言語設定を生成する
     *
     * @return static
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}