<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3MultidatabaseContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3MultidatabaseContentFactory extends Factory
{
    protected $model = Nc3MultidatabaseContent::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(2, true),
            'status' => 1,
            'is_latest' => 1,
            'is_active' => 1,
            'language_id' => 2, // 日本語
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            'is_latest' => 1,
            'is_active' => 1,
        ]);
    }

    public function forMultidatabase(int $multidatabase_id): static
    {
        return $this->state(fn (array $attributes) => [
            'multidatabase_id' => $multidatabase_id,
        ]);
    }

    public function withMultidatabaseKey(string $multidatabase_key): static
    {
        return $this->state(fn (array $attributes) => [
            'multidatabase_key' => $multidatabase_key,
        ]);
    }
}