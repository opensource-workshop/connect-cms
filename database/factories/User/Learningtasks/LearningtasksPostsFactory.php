<?php

namespace Database\Factories\User\Learningtasks;

use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksPosts;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningtasksPostsFactory extends Factory
{
    protected $model = LearningtasksPosts::class;

    public function definition()
    {
        return [
            'contents_id' => null,
            'learningtasks_id' => Learningtasks::factory(),
            'post_title' => $this->faker->sentence(),
            'post_text' => $this->faker->paragraph(),
            'post_text2' => $this->faker->paragraph(),
            'categories_id' => null,
            'important' => null,
            'use_canvas' => 0,
            'required_canvas_answer' => 0,
            'student_join_flag' => 2,
            'teacher_join_flag' => 2,
            'display_sequence' => $this->faker->unique()->randomNumber(),
            'status' => 0,
            'posted_at' => $this->faker->dateTime(),
        ];
    }
}
