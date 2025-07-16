<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Multidatabase;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3MultidatabaseFactory extends Factory
{
    protected $model = Nc3Multidatabase::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'name' => $this->faker->words(3, true),
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
}