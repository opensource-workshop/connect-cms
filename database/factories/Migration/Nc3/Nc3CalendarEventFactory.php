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
            'calendar_rrule_id' => 0,
            'key' => $this->faker->uuid(),
            'room_id' => 1, // Default room ID
            'language_id' => 2, // Japanese language
            'is_origin' => 1,
            'is_translation' => 0,
            'is_original_copy' => 0,
            'target_user' => 0,
            'title' => $this->faker->sentence(4),
            'title_icon' => '',
            'location' => $this->faker->address(),
            'contact' => $this->faker->email(),
            'description' => $this->faker->paragraph(3),
            'is_allday' => 0,
            'start_date' => $start_date->format('Ymd'),
            'start_time' => $start_date->format('His'),
            'dtstart' => $start_date->format('YmdHis'),
            'end_date' => $end_date->format('Ymd'),
            'end_time' => $end_date->format('His'),
            'dtend' => $end_date->format('YmdHis'),
            'timezone_offset' => 0.0,
            'status' => 1,
            'is_active' => 1,
            'is_latest' => 1,
            'recurrence_event_id' => 0,
            'exception_event_id' => 0,
            'is_enable_mail' => 0,
            'email_send_timing' => 0,
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forCalendar(int $calendar_id): static
    {
        // NC3では calendar_events は calendar_id フィールドを持たない
        // room_id で関連付けが行われる
        return $this->state(fn (array $attributes) => [
            // calendar_id は使用しない
        ]);
    }

    public function forRoom(int $room_id): static
    {
        return $this->state(fn (array $attributes) => [
            'room_id' => $room_id,
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
            'start_date' => $start_date->format('Ymd'),
            'start_time' => $start_date->format('His'),
            'dtstart' => $start_date->format('YmdHis'),
            'end_date' => $end_date->format('Ymd'),
            'end_time' => $end_date->format('His'),
            'dtend' => $end_date->format('YmdHis'),
        ]);
    }

    public function withRrule(int $calendar_rrule_id): static
    {
        return $this->state(fn (array $attributes) => [
            'calendar_rrule_id' => $calendar_rrule_id,
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