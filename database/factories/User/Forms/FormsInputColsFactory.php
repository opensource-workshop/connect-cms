<?php

namespace Database\Factories\User\Forms;

use App\Models\User\Forms\FormsInputCols;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormsInputColsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormsInputCols::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'forms_inputs_id' => null,
            'forms_columns_id' => null,
            'value' => $this->faker->word,
        ];
    }
}
