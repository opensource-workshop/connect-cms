<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3BbsFrameSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3BbsFrameSettingFactory extends Factory
{
    protected $model = Nc3BbsFrameSetting::class;

    public function definition(): array
    {
        return [
            'frame_key' => $this->faker->uuid(),
            'data_type_key' => 'content_id',
            'default_setting_action' => 'bbses/index',
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
}