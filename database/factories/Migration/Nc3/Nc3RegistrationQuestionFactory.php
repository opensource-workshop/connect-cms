<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3RegistrationQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3RegistrationQuestionFactory extends Factory
{
    protected $model = Nc3RegistrationQuestion::class;

    public function definition(): array
    {
        return [
            'question_sequence' => $this->faker->numberBetween(1, 10),
            'question_value' => $this->faker->sentence(),
            'question_type' => Nc3RegistrationQuestion::question_type_text,
            'is_require' => 0,
            'is_skip' => 0,
            'is_choice_random' => 0,
            'language_id' => 2, // 日本語
            'is_latest' => 1,
            'is_active' => 1,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forRegistration(int $registration_id): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_id' => $registration_id,
        ]);
    }

    public function withRegistrationKey(string $registration_key): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_key' => $registration_key,
        ]);
    }

    public function textType(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => Nc3RegistrationQuestion::question_type_text,
        ]);
    }

    public function textareaType(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => Nc3RegistrationQuestion::question_type_textarea,
        ]);
    }

    public function radioType(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => Nc3RegistrationQuestion::question_type_radio,
        ]);
    }

    public function checkboxType(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => Nc3RegistrationQuestion::question_type_checkbox,
        ]);
    }

    public function selectType(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => Nc3RegistrationQuestion::question_type_select,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_require' => 1,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_latest' => 1,
            'is_active' => 1,
        ]);
    }
}