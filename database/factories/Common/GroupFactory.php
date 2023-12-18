<?php

namespace Database\Factories\Common;

use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'display_sequence' => $this->faker->unique()->randomNumber(),
        ];
    }
}
