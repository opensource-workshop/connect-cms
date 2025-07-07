<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3ReservationLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3ReservationLocationFactory extends Factory
{
    protected $model = Nc3ReservationLocation::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->uuid(),
            'language_id' => 2, // Japanese
            'is_translation' => 0,
            'is_origin' => 1,
            'is_original_copy' => 0,
            'category_id' => 1,
            'location_name' => $this->faker->company() . '会議室',
            'detail' => $this->faker->paragraph(),
            'add_authority' => 0,
            'time_table' => 'Sun|Mon|Tue|Wed|Thu|Fri|Sat',
            'start_time' => '1970-01-01 00:00:00',
            'end_time' => '1970-01-01 23:59:59',
            'timezone' => 'Asia/Tokyo',
            'use_private' => 0,
            'use_auth_flag' => 0,
            'use_all_rooms' => 1,
            'use_workflow' => 0,
            'weight' => 1,
            'contact' => $this->faker->email(),
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forCategory(int $category_id): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category_id,
        ]);
    }

    public function withName(string $location_name): static
    {
        return $this->state(fn (array $attributes) => [
            'location_name' => $location_name,
        ]);
    }

    public function withWeight(int $weight): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $weight,
        ]);
    }

    public function withKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
        ]);
    }

    public function withTimeTable(string $time_table): static
    {
        return $this->state(fn (array $attributes) => [
            'time_table' => $time_table,
        ]);
    }

    public function privateReservation(): static
    {
        return $this->state(fn (array $attributes) => [
            'use_private' => 1,
        ]);
    }

    public function authRequired(): static
    {
        return $this->state(fn (array $attributes) => [
            'use_auth_flag' => 1,
        ]);
    }

    public function workflowEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'use_workflow' => 1,
        ]);
    }
}