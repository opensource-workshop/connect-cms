<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3UserAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3ユーザー項目定義テーブル用のFactory
 */
class Nc3UserAttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3UserAttribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language_id' => 2, // 日本語
            'key' => $this->faker->word(),
            'name' => $this->faker->words(2, true),
        ];
    }

    /**
     * テキスト項目を生成する
     *
     * @return static
     */
    public function textType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'テキスト項目',
            ];
        });
    }

    /**
     * メール項目を生成する
     *
     * @return static
     */
    public function emailType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'メールアドレス項目',
            ];
        });
    }

    /**
     * ラジオボタン項目を生成する
     *
     * @return static
     */
    public function radioType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'ラジオボタン項目',
            ];
        });
    }

    /**
     * テキストエリア項目を生成する
     *
     * @return static
     */
    public function textareaType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'テキストエリア項目',
            ];
        });
    }

    /**
     * セレクト項目を生成する
     *
     * @return static
     */
    public function selectType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'セレクト項目',
            ];
        });
    }

    /**
     * チェックボックス項目を生成する
     *
     * @return static
     */
    public function checkboxType(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'チェックボックス項目',
            ];
        });
    }

    /**
     * 必須項目を生成する
     *
     * @return static
     */
    public function required(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'required' => 1,
            ];
        });
    }

    /**
     * 管理者のみ閲覧可能な項目を生成する
     *
     * @return static
     */
    public function adminReadable(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'only_administrator_readable' => 1,
            ];
        });
    }
}