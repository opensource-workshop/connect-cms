<?php

namespace Database\Factories\User\Forms;

use App\Enums\FormColumnType;
use App\Models\User\Forms\FormsColumns;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormsColumnsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormsColumns::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'forms_id' => null,
            'column_type' => FormColumnType::text,
            'column_name' => $this->faker->word,
            'required' => 0,
            'frame_col' => 12,
            'caption' => null,
            'caption_color' => 'text-dark',
            'place_holder' => null,
            'minutes_increments' => 10,
            'minutes_increments_from' => 10,
            'minutes_increments_to' => 10,
            'rule_allowed_numeric' => 0,
            'rule_allowed_alpha_numeric' => 0,
            'rule_digits_or_less' => null,
            'rule_max' => null,
            'rule_min' => null,
            'rule_word_count' => null,
            'rule_date_after_equal' => null,
            'display_sequence' => 0,
        ];
    }

    /**
     * メールアドレス型のカラム
     */
    public function emailType()
    {
        return $this->state(function (array $attributes) {
            return [
                'column_type' => FormColumnType::mail,
                'column_name' => 'メールアドレス',
            ];
        });
    }

    /**
     * テキスト型のカラム
     */
    public function textType()
    {
        return $this->state(function (array $attributes) {
            return [
                'column_type' => FormColumnType::text,
            ];
        });
    }
}
