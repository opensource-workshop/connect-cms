<?php

namespace Database\Factories\User\Forms;

use App\Models\User\Forms\Forms;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Forms::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'bucket_id' => null,
            'forms_name' => $this->faker->sentence(3),
            'form_mode' => 'form',
            'access_limit_type' => 0,
            'form_password' => null,
            'entry_limit' => null,
            'entry_limit_over_message' => null,
            'display_control_flag' => 0,
            'display_from' => null,
            'display_to' => null,
            'regist_control_flag' => 0,
            'regist_from' => null,
            'regist_to' => null,
            'can_view_inputs_moderator' => 0,
            'mail_send_flag' => 0,
            'mail_send_address' => null,
            'user_mail_send_flag' => 0,
            'mail_subject' => null,
            'mail_format' => null,
            'data_save_flag' => 1,
            'after_message' => null,
            'numbering_use_flag' => 0,
            'numbering_prefix' => null,
            'use_spam_filter_flag' => 0,
            'spam_filter_message' => null,
        ];
    }

    /**
     * スパムフィルタリングを有効にする
     */
    public function withSpamFilter()
    {
        return $this->state(function (array $attributes) {
            return [
                'use_spam_filter_flag' => 1,
                'spam_filter_message' => $this->faker->sentence,
            ];
        });
    }
}
