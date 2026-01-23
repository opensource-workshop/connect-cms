<?php

namespace Database\Factories\User\Forms;

use App\Models\User\Forms\FormsInputs;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormsInputsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormsInputs::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'forms_id' => null,
            'status' => 0, // 本登録
            'ip_address' => null,
        ];
    }

    /**
     * IPアドレスを記録
     */
    public function withIpAddress($ip_address = null)
    {
        return $this->state(function (array $attributes) use ($ip_address) {
            return [
                'ip_address' => $ip_address ?? $this->faker->ipv4,
            ];
        });
    }
}
