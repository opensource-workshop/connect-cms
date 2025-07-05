<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3MultidatabaseMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3MultidatabaseMetadataFactory extends Factory
{
    protected $model = Nc3MultidatabaseMetadata::class;

    public function definition(): array
    {
        return [
            'col_name' => $this->faker->word(),
            'name' => $this->faker->words(2, true),
            'type' => 'text',
            'is_require' => 0,
            'is_searchable' => 1,
            'is_listable' => 1,
            'weight' => $this->faker->numberBetween(1, 100),
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
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

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_require' => 1,
        ]);
    }

    public function textType(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'text',
        ]);
    }

    public function textareaType(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'textarea',
        ]);
    }
}