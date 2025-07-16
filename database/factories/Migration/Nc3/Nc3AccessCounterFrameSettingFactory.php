<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3AccessCounterFrameSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3AccessCounterFrameSettingFactory extends Factory
{
    protected $model = Nc3AccessCounterFrameSetting::class;

    public function definition(): array
    {
        return [
            'frame_key' => $this->faker->uuid(),
            'data_type_key' => 'display_type',
            'value' => Nc3AccessCounterFrameSetting::display_type_default,
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

    public function defaultDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => Nc3AccessCounterFrameSetting::display_type_default,
        ]);
    }

    public function primaryDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => Nc3AccessCounterFrameSetting::display_type_primary,
        ]);
    }

    public function successDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => Nc3AccessCounterFrameSetting::display_type_success,
        ]);
    }

    public function infoDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => Nc3AccessCounterFrameSetting::display_type_info,
        ]);
    }

    public function warningDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => Nc3AccessCounterFrameSetting::display_type_warning,
        ]);
    }

    public function dangerDisplay(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'display_type',
            'value' => Nc3AccessCounterFrameSetting::display_type_danger,
        ]);
    }

    public function startCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'start_count',
            'value' => (string)$count,
        ]);
    }

    public function resetInterval(string $interval): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type_key' => 'reset_interval',
            'value' => $interval,
        ]);
    }
}