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
            'data_type_key' => 'display_type',
            'value' => 'month',
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

    public function displayType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => $type,
        ]);
    }

    public function monthView(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => 'month',
        ]);
    }

    public function weekView(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => 'week',
        ]);
    }

    public function dayView(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => 'day',
        ]);
    }

    public function listView(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => 'list',
        ]);
    }

    public function maxDisplayCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'max_display_count',
            'value' => (string)$count,
        ]);
    }

    public function showTitle(bool $show): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'show_title',
            'value' => $show ? '1' : '0',
        ]);
    }

    public function showDescription(bool $show): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'show_description',
            'value' => $show ? '1' : '0',
        ]);
    }
}