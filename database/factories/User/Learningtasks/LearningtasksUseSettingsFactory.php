<?php

namespace Database\Factories\User\Learningtasks;

use App\Enums\LearningtaskUseFunction;
use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksUseSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningtasksUseSettingsFactory extends Factory
{
    protected $model = LearningtasksUseSettings::class;

    public function definition()
    {
        return [
            'learningtasks_id' => Learningtasks::factory(),
            'post_id' => 0,
            'use_function' => $this->faker->randomElement(LearningtaskUseFunction::getMemberKeys()),
            'value' => $this->faker->randomElement(['on', 'off']),
            'datetime_value' => null,
        ];
    }
}
