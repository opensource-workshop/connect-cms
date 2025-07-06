<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3CalendarEventFactory extends Factory
{
    protected $model = Nc3CalendarEvent::class;

    public function definition(): array
    {
        $start_date = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end_date = clone $start_date;
        $end_date->modify('+2 hours');

        return [
            'key' => $this->faker->uuid(),
            'calendar_key' => $this->faker->uuid(),
            'title' => $this->faker->sentence(4),
            'title_icon' => '',
            'description' => $this->faker->paragraph(3),
            'location' => $this->faker->address(),
            'contact' => $this->faker->email(),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'is_allday' => 0,
            'timezone_offset' => 32400, // JST
            'is_repeat' => 0,
            'repeat_freq' => '',
            'rrule' => '',
            'status' => 1,
            'is_active' => 1,
            'is_latest' => 1,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forCalendar(string $calendar_key): static
    {
        return $this->state(fn (array $attributes) => [
            'calendar_key' => $calendar_key,
        ]);
    }

    public function withTitle(string $title): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
        ]);
    }

    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }

    public function withLocation(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => $location,
        ]);
    }

    public function withContact(string $contact): static
    {
        return $this->state(fn (array $attributes) => [
            'contact' => $contact,
        ]);
    }

    public function allday(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_allday' => 1,
        ]);
    }

    public function withDates(\DateTime $start_date, \DateTime $end_date): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    public function withRrule(string $rrule): static
    {
        return $this->state(fn (array $attributes) => [
            'is_repeat' => 1,
            'rrule' => $rrule,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 2,
        ]);
    }
}