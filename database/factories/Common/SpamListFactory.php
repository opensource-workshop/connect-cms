<?php

namespace Database\Factories\Common;

use App\Enums\SpamBlockType;
use App\Models\Common\SpamList;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpamListFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpamList::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'target_plugin_name' => 'forms',
            'target_id' => null,
            'block_type' => SpamBlockType::email,
            'block_value' => $this->faker->email,
            'memo' => $this->faker->sentence,
        ];
    }

    /**
     * グローバルスコープ（全体適用）のスパムリスト
     */
    public function global()
    {
        return $this->state(function (array $attributes) {
            return [
                'target_id' => null,
            ];
        });
    }

    /**
     * 特定フォーム用のスパムリスト
     */
    public function forForm($forms_id)
    {
        return $this->state(function (array $attributes) use ($forms_id) {
            return [
                'target_id' => $forms_id,
            ];
        });
    }

    /**
     * メールアドレス型のスパムリスト
     */
    public function emailType()
    {
        return $this->state(function (array $attributes) {
            return [
                'block_type' => SpamBlockType::email,
                'block_value' => $this->faker->email,
            ];
        });
    }

    /**
     * ドメイン型のスパムリスト
     */
    public function domainType()
    {
        return $this->state(function (array $attributes) {
            return [
                'block_type' => SpamBlockType::domain,
                'block_value' => $this->faker->domainName,
            ];
        });
    }

    /**
     * IPアドレス型のスパムリスト
     */
    public function ipAddressType()
    {
        return $this->state(function (array $attributes) {
            return [
                'block_type' => SpamBlockType::ip_address,
                'block_value' => $this->faker->ipv4,
            ];
        });
    }
}
