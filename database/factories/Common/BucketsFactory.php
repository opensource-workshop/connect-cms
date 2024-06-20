<?php

namespace Database\Factories\Common;

use App\Enums\PluginName;
use Illuminate\Database\Eloquent\Factories\Factory;

class BucketsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'bucket_name' => $this->faker->title(),
            'plugin_name' => PluginName::getPluginName(array_rand(PluginName::enum)),
            'container_page_id' => null,
        ];
    }
}
