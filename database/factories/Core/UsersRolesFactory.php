<?php

namespace Database\Factories\Core;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsersRolesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'users_id' => User::factory(),
            'target' => 'base',
            'role_name' => 'role_guest',
            'role_value' => 1,
        ];
    }
}
