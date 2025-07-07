<?php

namespace Database\Factories\Migration\Nc3;

use App\Models\Migration\Nc3\Nc3UploadFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NC3アップロードファイルテーブル用のFactory
 */
class Nc3UploadFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Nc3UploadFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => $this->faker->numberBetween(1, 10),
            'original_name' => $this->faker->words(2, true) . '.jpg',
            'real_file_name' => $this->faker->uuid . '.jpg',
            'path' => 'files/' . $this->faker->date('Y/m/d') . '/',
            'size' => $this->faker->numberBetween(1000, 1000000),
            'mimetype' => 'image/jpeg',
            'extension' => 'jpg',
            'plugin_key' => $this->faker->randomElement(['blogs', 'cabinets', 'photos']),
            'created' => $this->faker->dateTime(),
            'modified' => $this->faker->dateTime(),
        ];
    }

    /**
     * 画像ファイルを生成する
     *
     * @return static
     */
    public function imageFile(): static
    {
        return $this->state(function (array $attributes) {
            $extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = $this->faker->randomElement($extensions);
            
            return [
                'original_name' => $this->faker->words(2, true) . '.' . $extension,
                'real_file_name' => $this->faker->uuid . '.' . $extension,
                'mimetype' => 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension),
                'extension' => $extension,
            ];
        });
    }

    /**
     * PDFファイルを生成する
     *
     * @return static
     */
    public function pdfFile(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'original_name' => $this->faker->words(2, true) . '.pdf',
                'real_file_name' => $this->faker->uuid . '.pdf',
                'mimetype' => 'application/pdf',
                'extension' => 'pdf',
            ];
        });
    }

    /**
     * 特定のルームIDを指定してファイルを生成する
     *
     * @param int $roomId
     * @return static
     */
    public function forRoom(int $roomId): static
    {
        return $this->state(function (array $attributes) use ($roomId) {
            return [
                'room_id' => $roomId,
            ];
        });
    }

    /**
     * ブログプラグイン用ファイルを生成する
     *
     * @return static
     */
    public function forBlog(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plugin_key' => 'blogs',
            ];
        });
    }

    /**
     * キャビネットプラグイン用ファイルを生成する
     *
     * @return static
     */
    public function forCabinet(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'plugin_key' => 'cabinets',
            ];
        });
    }

    /**
     * 特定のコンテンツに紐づくファイルを生成する
     *
     * @param string $contentKey
     * @param string $pluginKey
     * @param string $fieldName
     * @return static
     */
    public function forContent(string $contentKey, string $pluginKey, string $fieldName): static
    {
        return $this->state(function (array $attributes) use ($contentKey, $pluginKey, $fieldName) {
            return [
                'content_key' => $contentKey,
                'plugin_key' => $pluginKey,
                'field_name' => $fieldName,
            ];
        });
    }

    /**
     * 大容量ファイルを生成する
     *
     * @return static
     */
    public function largeFile(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'size' => $this->faker->numberBetween(10000000, 50000000), // 10MB-50MB
            ];
        });
    }

    /**
     * 特定のパスを指定してファイルを生成する
     *
     * @param string $path
     * @return static
     */
    public function withPath(string $path): static
    {
        return $this->state(function (array $attributes) use ($path) {
            return [
                'path' => $path,
            ];
        });
    }
}