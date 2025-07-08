<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3AccessCounter;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3AccessCounterFactory extends Factory
{
    protected $model = Nc3AccessCounter::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'name' => $this->faker->words(3, true),
            'count' => $this->faker->numberBetween(1, 100000),
            'display_type' => 1, // default
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

    public function withCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'count' => $count,
        ]);
    }

    public function defaultDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 1,
        ]);
    }

    public function primaryDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 2,
        ]);
    }

    public function successDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 3,
        ]);
    }

    public function infoDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 4,
        ]);
    }

    public function warningDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 5,
        ]);
    }

    public function dangerDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 6,
        ]);
    }
}