<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3CalendarFactory extends Factory
{
    protected $model = Nc3Calendar::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'name' => $this->faker->words(3, true),
            'status' => 1,
            'is_active' => 1,
            'is_latest' => 1,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => 1,
            'is_latest' => 1,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => 0,
        ]);
    }

    public function withKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
        ]);
    }

    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 2,
        ]);
    }
}