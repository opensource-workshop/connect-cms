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
            'block_key' => $this->faker->uuid(),
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            // NC3 Calendar table has no status field
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            // NC3 Calendar table has no status field
        ]);
    }

    public function withBlockKey(string $block_key): static
    {
        return $this->state(fn (array $attributes) => [
            'block_key' => $block_key,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            // NC3 Calendar table has no status field
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            // NC3 Calendar table has no status field
        ]);
    }
}