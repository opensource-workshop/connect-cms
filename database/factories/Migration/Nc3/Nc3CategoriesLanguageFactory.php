<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3CategoriesLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3CategoriesLanguageFactory extends Factory
{
    protected $model = Nc3CategoriesLanguage::class;

    public function definition(): array
    {
        return [
            'category_id' => 1,
            'language_id' => 2, // Japanese
            'name' => $this->faker->words(2, true) . 'カテゴリ',
            'is_origin' => 1,
            'is_translation' => 0,
            'is_original_copy' => 0,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forCategory(int $category_id): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category_id,
        ]);
    }

    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    public function japanese(): static
    {
        return $this->state(fn (array $attributes) => [
            'language_id' => 2,
        ]);
    }
}