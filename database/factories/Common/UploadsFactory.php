<?php

namespace Database\Factories\Common;

use App\Models\Common\Uploads;
use Illuminate\Database\Eloquent\Factories\Factory;

class UploadsFactory extends Factory
{
    protected $model = Uploads::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $extension = $this->faker->fileExtension();
        $client_original_name = $this->faker->word() . '.' . $extension;

        return [
            'client_original_name' => $client_original_name,
            'mimetype' => $this->faker->mimeType(),
            'extension' => $extension,
            'size' => $this->faker->randomNumber(5, false),
            'plugin_name' => 'blogs',
            'download_count' => 0,
            'page_id' => 0,
            'private' => 0,
            'temporary_flag' => 0,
            'check_method' => null,
            'created_id' => null,
            'created_name' => null,
            'updated_id' => null,
            'updated_name' => null
        ];
    }

    /**
     * JPGのデータ
     */
    public function jpg()
    {
        return $this->state(function (array $attributes) {
            return [
                'client_original_name' => $this->faker->word() . '.jpg',
                'mimetype' => 'image/jpeg',
                'extension' => 'jpg',
            ];
        });
    }
}
