<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3CalendarFrameSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3CalendarFrameSettingFactory extends Factory
{
    protected $model = Nc3CalendarFrameSetting::class;

    public function definition(): array
    {
        return [
            'frame_key' => $this->faker->uuid(),
            'display_type' => 0, // 0=month, 1=week, 2=day, 3=list
            'start_pos' => 0,
            'display_count' => 0,
            'is_myroom' => 0,
            'is_select_room' => 0,
            'timeline_base_time' => 0,
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

    public function displayType(int $type): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => $type,
        ]);
    }

    public function monthView(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 0,
        ]);
    }

    public function weekView(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 1,
        ]);
    }

    public function dayView(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 2,
        ]);
    }

    public function listView(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => 3,
        ]);
    }

    public function displayCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'display_count' => $count,
        ]);
    }

    public function myRoom(bool $is_myroom = true): static
    {
        return $this->state(fn (array $attributes) => [
            'is_myroom' => $is_myroom ? 1 : 0,
        ]);
    }

    public function selectRoom(bool $is_select_room = true): static
    {
        return $this->state(fn (array $attributes) => [
            'is_select_room' => $is_select_room ? 1 : 0,
        ]);
    }
}