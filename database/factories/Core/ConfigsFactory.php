<?php

namespace Database\Factories\Core;

use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigsFactory extends Factory
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
            'value' => $this->faker->word(),
            'category' => $this->faker->word(),
            'additional1' => null,
            'additional2' => null,
            'additional3' => null,
            'additional4' => null,
            'additional5' => null,
        ];
    }
}
