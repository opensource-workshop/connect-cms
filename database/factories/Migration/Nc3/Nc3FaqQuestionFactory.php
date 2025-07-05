<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3FaqQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3FaqQuestionFactory extends Factory
{
    protected $model = Nc3FaqQuestion::class;

    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence() . '？',
            'answer' => $this->faker->paragraphs(2, true),
            'status' => 1,
            'is_latest' => 1,
            'is_active' => 1,
            'language_id' => 2, // 日本語
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            'is_latest' => 1,
            'is_active' => 1,
        ]);
    }

    public function forFaq(int $faq_id): static
    {
        return $this->state(fn (array $attributes) => [
            'faq_id' => $faq_id,
        ]);
    }

    public function withFaqKey(string $faq_key): static
    {
        return $this->state(fn (array $attributes) => [
            'faq_key' => $faq_key,
        ]);
    }
}