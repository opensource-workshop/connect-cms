<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3CategoryOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3CategoryOrderFactory extends Factory
{
    protected $model = Nc3CategoryOrder::class;

    public function definition(): array
    {
        return [
            'category_key' => $this->faker->uuid(),
            'block_key' => $this->faker->uuid(),
            'weight' => 1,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forCategory(string $category_key): static
    {
        return $this->state(fn (array $attributes) => [
            'category_key' => $category_key,
        ]);
    }

    public function forBlock(string $block_key): static
    {
        return $this->state(fn (array $attributes) => [
            'block_key' => $block_key,
        ]);
    }

    public function withWeight(int $weight): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $weight,
        ]);
    }
}