<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3RegistrationAnswerSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3RegistrationAnswerSummaryFactory extends Factory
{
    protected $model = Nc3RegistrationAnswerSummary::class;

    public function definition(): array
    {
        return [
            'answer_value' => $this->faker->sentence(),
            'summary_value' => $this->faker->word(),
            'answer_number' => $this->faker->numberBetween(1, 100),
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

    public function forQuestion(int $registration_question_id): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_question_id' => $registration_question_id,
        ]);
    }
}