<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3RegistrationChoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3RegistrationChoiceFactory extends Factory
{
    protected $model = Nc3RegistrationChoice::class;

    public function definition(): array
    {
        return [
            'choice_sequence' => $this->faker->numberBetween(1, 5),
            'choice_label' => $this->faker->words(2, true),
            'choice_value' => $this->faker->word(),
            'language_id' => 2, // 日本語
            'is_latest' => 1,
            'is_active' => 1,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forQuestion(int $registration_question_id): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_question_id' => $registration_question_id,
        ]);
    }

    public function withRegistrationKey(string $registration_key): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_key' => $registration_key,
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