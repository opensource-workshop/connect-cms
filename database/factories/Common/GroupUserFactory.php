<?php

namespace Database\Factories\Common;

use App\Models\Common\Group;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group_id' => Group::factory()->create(),
            'user_id' => User::factory()->create(),
        ];
    }
}
