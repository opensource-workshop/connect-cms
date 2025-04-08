<?php

namespace Database\Factories\User\Learningtasks;

use App\Models\Common\Buckets;
use App\Models\User\Learningtasks\Learningtasks;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningtasksFactory extends Factory
{
    protected $model = Learningtasks::class;

    public function definition()
    {
        return [
            'bucket_id' => Buckets::factory(),
            'learningtasks_name' => $this->faker->word,
            'view_count' => $this->faker->numberBetween(0, 100),
            'rss' => 0,
            'rss_count' => $this->faker->numberBetween(0, 50),
            'sequence_conditions' => $this->faker->numberBetween(0, 50),
        ];
    }
}
