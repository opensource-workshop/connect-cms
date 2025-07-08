<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3TopicFactory extends Factory
{
    protected $model = Nc3Topic::class;

    public function definition(): array
    {
        return [
            'plugin_key' => 'blogs',
            'content_key' => $this->faker->uuid(),
            'block_id' => $this->faker->numberBetween(1, 100),
            'room_id' => $this->faker->numberBetween(1, 50),
            'title' => $this->faker->sentence(),
            'summary' => $this->faker->paragraph(),
            'path' => '/' . $this->faker->slug(),
            'published_datetime' => $this->faker->dateTime(),
            'status' => 1,
            'is_latest' => 1,
            'is_active' => 1,
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

    public function forPlugin(string $plugin_key): static
    {
        return $this->state(fn (array $attributes) => [
            'plugin_key' => $plugin_key,
        ]);
    }

    public function withContentKey(string $content_key): static
    {
        return $this->state(fn (array $attributes) => [
            'content_key' => $content_key,
        ]);
    }

    public function forRoom(int $room_id): static
    {
        return $this->state(fn (array $attributes) => [
            'room_id' => $room_id,
        ]);
    }

    public function blogTopic(): static
    {
        return $this->state(fn (array $attributes) => [
            'plugin_key' => 'blogs',
        ]);
    }

    public function bbsTopic(): static
    {
        return $this->state(fn (array $attributes) => [
            'plugin_key' => 'bbses',
        ]);
    }

    public function faqTopic(): static
    {
        return $this->state(fn (array $attributes) => [
            'plugin_key' => 'faqs',
        ]);
    }
}