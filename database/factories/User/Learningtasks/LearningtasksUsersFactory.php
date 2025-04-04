<?php

namespace Database\Factories\User\Learningtasks;

use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsers;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningtasksUsersFactory extends Factory
{
    protected $model = LearningtasksUsers::class;

    public function definition()
    {
        return [
            'post_id' => LearningtasksPosts::factory(),
            'user_id' => User::factory(),
            'role_name' => $this->faker->randomElement(['student', 'teacher']),
        ];
    }
}
