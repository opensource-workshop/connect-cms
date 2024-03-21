<?php

namespace Database\Factories\Common;

use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $page_name = $this->faker->word();
        return [
            'page_name'      => $page_name,
            'permanent_link' => "/{$page_name}",
        ];
    }
}
