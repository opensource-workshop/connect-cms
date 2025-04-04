<?php

namespace Database\Factories\User\Learningtasks;

use App\Models\User\Learningtasks\LearningtasksPosts;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningtasksUsersStatusesFactory extends Factory
{
    public function definition()
    {
        return [
            'post_id' => LearningtasksPosts::factory(),
            'user_id' => User::factory(),
            'task_status' => 1, // 提出
            'comment' => $this->faker->sentence,
            'upload_id' => null,
            'examination_id' => null,
            'grade' => null,
        ];
    }
}
