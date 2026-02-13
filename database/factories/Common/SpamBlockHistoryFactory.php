<?php

namespace Database\Factories\Common;

use App\Enums\SpamBlockType;
use App\Models\Common\SpamBlockHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpamBlockHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpamBlockHistory::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'spam_list_id' => null,
            'forms_id' => null,
            'block_type' => SpamBlockType::email,
            'block_value' => $this->faker->email,
            'client_ip' => $this->faker->ipv4,
            'submitted_email' => $this->faker->email,
        ];
    }

    /**
     * メールアドレス型のブロック履歴
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
     * ドメイン型のブロック履歴
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
     * IPアドレス型のブロック履歴
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

    /**
     * 特定フォームに紐付け
     */
    public function forForm($forms_id)
    {
        return $this->state(function (array $attributes) use ($forms_id) {
            return [
                'forms_id' => $forms_id,
            ];
        });
    }

    /**
     * 特定のスパムルールに紐付け
     */
    public function withSpamList($spam_list_id)
    {
        return $this->state(function (array $attributes) use ($spam_list_id) {
            return [
                'spam_list_id' => $spam_list_id,
            ];
        });
    }
}
