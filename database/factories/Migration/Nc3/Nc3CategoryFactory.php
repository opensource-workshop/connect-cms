<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3CategoryFactory extends Factory
{
    protected $model = Nc3Category::class;

    public function definition(): array
    {
        return [
            'block_id' => 1,
            'key' => $this->faker->uuid(),
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forBlock(int $block_id): static
    {
        return $this->state(fn (array $attributes) => [
            'block_id' => $block_id,
        ]);
    }

    public function withKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
        ]);
    }
}