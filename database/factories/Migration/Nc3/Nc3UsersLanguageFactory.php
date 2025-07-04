<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3UsersLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ユーザー多言語情報テーブル用のFactory
 */
class Nc3UsersLanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3UsersLanguage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 100),
            'language_id' => 2, // 日本語
            'name' => $this->faker->name(),
            'profile' => $this->faker->paragraph(),
            'search_keywords' => implode(',', $this->faker->words(3)),
        ];
    }

    /**
     * 特定のユーザー用の多言語情報を生成する
     *
     * @param int $userId
     * @return static
     */
    public function forUser(int $userId): static
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'user_id' => $userId,
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
                'name' => '田中太郎',
                'profile' => 'こんにちは。田中太郎です。',
                'search_keywords' => '田中,太郎,テスト',
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
                'name' => 'Taro Tanaka',
                'profile' => 'Hello. I am Taro Tanaka.',
                'search_keywords' => 'Taro,Tanaka,Test',
            ];
        });
    }

    /**
     * プロフィールなしの情報を生成する
     *
     * @return static
     */
    public function noProfile(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'profile' => '',
                'search_keywords' => '',
            ];
        });
    }
}