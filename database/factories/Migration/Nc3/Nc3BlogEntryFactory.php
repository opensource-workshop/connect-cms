<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3BlogEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ブログエントリテーブル用のFactory
 */
class Nc3BlogEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3BlogEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'language_id' => 2, // 日本語
            'status' => 1, // 公開
            'is_active' => 1,
            'is_latest' => 1,
            'title' => $this->faker->sentence(),
            'body1' => $this->faker->paragraphs(3, true),
            'body2' => $this->faker->paragraph(),
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * 公開状態のエントリを生成する
     *
     * @return static
     */
    public function published(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 1,
                'is_active' => 1,
                'is_latest' => 1,
            ];
        });
    }

    /**
     * 一時保存状態のエントリを生成する
     *
     * @return static
     */
    public function draft(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 2,
            ];
        });
    }

    /**
     * 承認待ち状態のエントリを生成する
     *
     * @return static
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 3,
            ];
        });
    }

    /**
     * 特定のブログに属するエントリを生成する
     *
     * @param int $blogId
     * @return static
     */
    public function forBlog(int $blogId): static
    {
        return $this->state(function (array $attributes) use ($blogId) {
            return [
                'content_key' => "blog_{$blogId}_content",
            ];
        });
    }

    /**
     * 特定のユーザーが作成したエントリを生成する
     *
     * @param int $userId
     * @return static
     */
    public function byUser(int $userId): static
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'created_user' => $userId,
                'modified_user' => $userId,
            ];
        });
    }

    /**
     * 日本語のエントリを生成する
     *
     * @return static
     */
    public function japanese(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'language_id' => 2,
            ];
        });
    }

    /**
     * 英語のエントリを生成する
     *
     * @return static
     */
    public function english(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'language_id' => 1,
            ];
        });
    }
}