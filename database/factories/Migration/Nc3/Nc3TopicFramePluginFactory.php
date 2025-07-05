<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3TopicFramePlugin;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3TopicFramePluginFactory extends Factory
{
    protected $model = Nc3TopicFramePlugin::class;

    public function definition(): array
    {
        return [
            'frame_key' => $this->faker->uuid(),
            'plugin_key' => 'topics',
            'is_enabled' => 1,
            'weight' => $this->faker->numberBetween(1, 100),
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => 1,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => 0,
        ]);
    }

    public function forFrame(string $frame_key): static
    {
        return $this->state(fn (array $attributes) => [
            'frame_key' => $frame_key,
        ]);
    }

    public function forPlugin(string $plugin_key): static
    {
        return $this->state(fn (array $attributes) => [
            'plugin_key' => $plugin_key,
        ]);
    }
}