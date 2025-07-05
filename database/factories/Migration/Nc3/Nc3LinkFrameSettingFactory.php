<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3LinkFrameSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3LinkFrameSettingFactory extends Factory
{
    protected $model = Nc3LinkFrameSetting::class;

    public function definition(): array
    {
        return [
            'frame_key' => $this->faker->uuid(),
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forContent(string $content_key): static
    {
        return $this->state(fn (array $attributes) => [
            'content_key' => $content_key,
        ]);
    }

    public function withFrameKey(string $frame_key): static
    {
        return $this->state(fn (array $attributes) => [
            'frame_key' => $frame_key,
        ]);
    }
}