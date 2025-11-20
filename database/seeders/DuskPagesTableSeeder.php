<?php

namespace Database\Seeders;

use App\Models\Common\Page;
use Illuminate\Database\Seeder;

/**
 * Duskの単独実行時にページを登録するシーダー
 */
class DuskPagesTableSeeder extends Seeder
{
    public function run(): void
    {
        $records = $this->loadRecords();

        foreach ($records as $record) {
            $attributes = [
                'page_name' => $record['page_name'],
                'background_color' => $record['background_color'],
                'header_color' => $record['header_color'],
                'theme' => $record['theme'],
                'layout' => $record['layout'],
                'base_display_flag' => 1,
                'membership_flag' => 0,
                'container_flag' => 0,
            ];

            $page = Page::where('permanent_link', $record['permanent_link'])->first();

            if ($page) {
                $page->fill($attributes);

                if ($page->isDirty()) {
                    $page->save();
                }

                continue;
            }

            Page::create(array_merge($attributes, [
                'permanent_link' => $record['permanent_link'],
            ]));
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadRecords(): array
    {
        $path = base_path('tests/Browser/Manage/page.csv');

        if (! is_file($path)) {
            return [];
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return [];
        }

        $records = [];

        while (($row = fgetcsv($handle)) !== false) {
            $row = $this->convertEncoding($row);
            $data = array_combine($header, $row);

            $records[] = [
                'page_name' => $data['page_name'] ?? '',
                'permanent_link' => $data['permanent_link'] ?? '/',
                'background_color' => $this->nullIfString($data['background_color'] ?? null),
                'header_color' => $this->nullIfString($data['header_color'] ?? null),
                'theme' => $this->nullIfString($data['theme'] ?? null),
                'layout' => $this->nullIfString($data['layout'] ?? null),
            ];
        }

        fclose($handle);

        return $records;
    }

    /**
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    private function convertEncoding(array $values): array
    {
        return array_map(function ($value) {
            return mb_convert_encoding($value, 'UTF-8', 'SJIS-win');
        }, $values);
    }

    private function nullIfString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtoupper($value) === 'NULL' ? null : $value;
    }
}
