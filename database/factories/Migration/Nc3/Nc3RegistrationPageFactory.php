<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3RegistrationPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3RegistrationPageFactory extends Factory
{
    protected $model = Nc3RegistrationPage::class;

    public function definition(): array
    {
        return [
            'page_sequence' => $this->faker->numberBetween(1, 3),
            'page_title' => $this->faker->words(3, true),
            'route_number' => $this->faker->numberBetween(1, 10),
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

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_latest' => 1,
            'is_active' => 1,
        ]);
    }
}