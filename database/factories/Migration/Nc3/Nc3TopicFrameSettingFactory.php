<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3TopicFrameSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3TopicFrameSettingFactory extends Factory
{
    protected $model = Nc3TopicFrameSetting::class;

    public function definition(): array
    {
        return [
            'frame_key' => $this->faker->uuid(),
            'data_type_key' => 'content_per_page',
            'value' => '10',
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forFrame(string $frame_key): static
    {
        return $this->state(fn (array $attributes) => [
            'frame_key' => $frame_key,
        ]);
    }

    public function contentPerPage(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'content_per_page',
            'value' => (string)$count,
        ]);
    }

    public function displayType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => $type,
        ]);
    }

    public function sortType(string $sort): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'sort_type',
            'value' => $sort,
        ]);
    }
}