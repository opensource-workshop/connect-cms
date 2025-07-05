<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3CabinetFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class Nc3CabinetFileFactory extends Factory
{
    protected $model = Nc3CabinetFile::class;

    public function definition(): array
    {
        return [
            'filename' => $this->faker->word() . '.pdf',
            'original_name' => $this->faker->word() . '.pdf',
            'extension' => 'pdf',
            'mimetype' => 'application/pdf',
            'size' => $this->faker->numberBetween(1024, 10485760), // 1KB - 10MB
            'description' => $this->faker->sentence(),
            'download_count' => $this->faker->numberBetween(0, 100),
            'status' => 1,
            'is_folder' => 0,
            'folder_id' => null,
            'is_latest' => 1,
            'is_active' => 1,
            'language_id' => 2, // 日本語
            'created_user' => 1,
            'created' => $this->faker->dateTime(),
            'modified_user' => 1,
            'modified' => $this->faker->dateTime(),
        ];
    }

    public function forCabinet(int $cabinet_id): static
    {
        return $this->state(fn (array $attributes) => [
            'cabinet_id' => $cabinet_id,
        ]);
    }

    public function withCabinetKey(string $cabinet_key): static
    {
        return $this->state(fn (array $attributes) => [
            'cabinet_key' => $cabinet_key,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            'is_latest' => 1,
            'is_active' => 1,
        ]);
    }

    public function asFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_folder' => 0,
        ]);
    }

    public function asFolder(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_folder' => 1,
            'filename' => $this->faker->word(),
            'original_name' => $this->faker->word(),
            'extension' => '',
            'mimetype' => '',
            'size' => 0,
        ]);
    }

    public function inFolder(int $folder_id): static
    {
        return $this->state(fn (array $attributes) => [
            'folder_id' => $folder_id,
        ]);
    }

    public function pdfFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'filename' => $this->faker->word() . '.pdf',
            'original_name' => $this->faker->word() . '.pdf',
            'extension' => 'pdf',
            'mimetype' => 'application/pdf',
        ]);
    }

    public function docFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'filename' => $this->faker->word() . '.doc',
            'original_name' => $this->faker->word() . '.doc',
            'extension' => 'doc',
            'mimetype' => 'application/msword',
        ]);
    }

    public function imageFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'filename' => $this->faker->word() . '.jpg',
            'original_name' => $this->faker->word() . '.jpg',
            'extension' => 'jpg',
            'mimetype' => 'image/jpeg',
        ]);
    }
}