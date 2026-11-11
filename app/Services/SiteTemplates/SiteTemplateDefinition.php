<?php

namespace App\Services\SiteTemplates;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * リポジトリに同梱した定義・本文を読み込み、保存前に参照関係を検証する。
 */
class SiteTemplateDefinition
{
    public const PLUGIN_LABELS = ['contents' => '固定記事', 'menus' => 'メニュー', 'blogs' => 'ブログ', 'calendars' => 'カレンダー'];

    public function load(string $id): array
    {
        if ($id !== 'school-basic') {
            throw new InvalidArgumentException('指定されたサイトひな型はありません。');
        }
        $source = file_get_contents(resource_path('site_templates/school-basic/template.json'));
        if (strlen($source) > 8388608) {
            throw new InvalidArgumentException('サイトひな型の定義が上限を超えています。');
        }
        $definition = json_decode($source, true, 512, JSON_THROW_ON_ERROR);
        $this->validate($definition);
        if ($definition['id'] !== $id) {
            throw new InvalidArgumentException('登録されたサイトひな型と定義が一致しません。');
        }
        $definition['checksum'] = hash('sha256', $source);
        return $definition;
    }

    public function validate(array $definition): void
    {
        $fail = function () {
            throw new InvalidArgumentException('サイトひな型の定義が不正です。');
        };
        $rules = [
            'schema_version' => 'required|integer', 'id' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:1000', 'required_plugins' => 'required|array',
            'required_plugins.*' => 'required|string|distinct',
            'pages' => 'required|array|min:1|max:50', 'buckets' => 'required|array|min:1|max:100',
            'frames' => 'required|array|min:1|max:200', 'menus' => 'required|array|max:200', 'contents' => 'required|array|max:100',
            'pages.*' => 'required|array', 'buckets.*' => 'required|array', 'frames.*' => 'required|array',
            'pages.*.name' => 'required|string|max:100', 'pages.*.url' => 'required|string|max:255',
            'pages.*.parent' => 'nullable|string', 'buckets.*.name' => 'required|string|max:100',
            'buckets.*.plugin' => 'required|string', 'frames.*.page' => 'required|string',
            'frames.*.bucket' => 'nullable|string', 'frames.*.plugin' => 'required|string', 'frames.*.template' => 'required|string',
            'menus.*' => 'required|array', 'menus.*.frame' => 'required|string', 'menus.*.pages' => 'required|array',
            'menus.*.pages.*' => 'required|string',
            'contents.*' => 'required|array', 'contents.*.bucket' => 'required|string', 'contents.*.html' => 'required|string',
        ];
        $allowed = ['schema_version', 'id', 'name', 'description', 'required_plugins', 'pages', 'buckets', 'frames', 'menus', 'contents'];
        if (Validator::make($definition, $rules)->fails() || array_diff(array_keys($definition), $allowed) || $definition['schema_version'] !== 1) {
            $fail();
        }
        $schemas = [
            'pages' => ['key', 'name', 'url', 'parent', 'layout'],
            'buckets' => ['key', 'name', 'plugin'],
            'frames' => ['key', 'page', 'area', 'bucket', 'plugin', 'template', 'sequence'],
        ];
        $maps = [];
        foreach ($schemas as $type => $keys) {
            foreach ($definition[$type] as $row) {
                if (array_diff(array_keys($row), $keys) || array_diff($keys, array_keys($row)) ||
                    !is_string($row['key']) || !preg_match('/^[a-z][a-z0-9_]{0,99}$/D', $row['key']) || isset($maps[$type][$row['key']])) {
                    $fail();
                }
                $maps[$type][$row['key']] = $row;
            }
        }
        $plugins = array_keys(self::PLUGIN_LABELS);
        if (array_diff($definition['required_plugins'], $plugins)) {
            $fail();
        }
        $urls = [];
        foreach ($maps['pages'] as $key => $page) {
            if (!preg_match('~^/(?:[a-z0-9-]+(?:/[a-z0-9-]+)*)?$~D', $page['url']) || isset($urls[$page['url']])) {
                $fail();
            }
            $urls[$page['url']] = true;
            if ($key === 'home') {
                if ($page['url'] !== '/' || $page['parent'] !== null || $page['layout'] !== '1|1|0|1') {
                    $fail();
                }
            } elseif ($page['url'] === '/' || $page['layout'] !== null) {
                $fail();
            }
            $visited = [$key => true];
            $parent = $page['parent'];
            while ($parent !== null) {
                if (!isset($maps['pages'][$parent]) || isset($visited[$parent])) {
                    $fail();
                }
                $visited[$parent] = true;
                $parent = $maps['pages'][$parent]['parent'];
            }
        }
        if ($definition['pages'][0]['key'] !== 'home') {
            $fail();
        }
        foreach ($maps['buckets'] as $bucket) {
            if (!in_array($bucket['plugin'], ['contents', 'blogs', 'calendars'], true)) {
                $fail();
            }
        }
        $sequences = [];
        foreach ($maps['frames'] as $frame) {
            if (!isset($maps['pages'][$frame['page']]) || !in_array($frame['area'], [0, 1, 2, 3, 4], true) ||
                !is_int($frame['sequence']) || $frame['sequence'] < 1 || !in_array($frame['plugin'], $definition['required_plugins'], true) ||
                !in_array($frame['template'], $frame['plugin'] === 'menus' ? ['default', 'tab'] : ['default'], true)) {
                $fail();
            }
            $sequence = $frame['page'] . ':' . $frame['sequence'];
            if (isset($sequences[$sequence])) {
                $fail();
            }
            $sequences[$sequence] = true;
            if ($frame['plugin'] === 'menus') {
                if ($frame['bucket'] !== null) {
                    $fail();
                }
            } elseif (($maps['buckets'][$frame['bucket']]['plugin'] ?? null) !== $frame['plugin']) {
                $fail();
            }
        }
        $seen = [];
        foreach ($definition['menus'] as $menu) {
            if (array_diff(array_keys($menu), ['frame', 'pages']) || ($maps['frames'][$menu['frame']]['plugin'] ?? '') !== 'menus' ||
                isset($seen[$menu['frame']]) || count($menu['pages']) !== count(array_unique($menu['pages'])) ||
                array_diff($menu['pages'], array_keys($maps['pages']))) {
                $fail();
            }
            $seen[$menu['frame']] = true;
        }
        foreach ($maps['frames'] as $key => $frame) {
            if ($frame['plugin'] === 'menus' && !isset($seen[$key])) {
                $fail();
            }
        }
        $seen = [];
        $html_bytes = 0;
        foreach ($definition['contents'] as $content) {
            if (array_diff(array_keys($content), ['bucket', 'html']) || ($maps['buckets'][$content['bucket']]['plugin'] ?? '') !== 'contents' ||
                isset($seen[$content['bucket']])) {
                $fail();
            }
            $html_bytes += strlen($content['html']);
            if ($html_bytes > 1048576) {
                throw new InvalidArgumentException('サイトひな型の本文が上限を超えています。');
            }
            $seen[$content['bucket']] = true;
        }
        foreach ($maps['buckets'] as $key => $bucket) {
            if ($bucket['plugin'] === 'contents' && !isset($seen[$key])) {
                $fail();
            }
        }
    }

    /**
     * サイト名だけをHTMLエスケープして差し込む。BladeやPHPとしては実行しない。
     */
    public function renderHtml(string $html, string $site_name): string
    {
        return str_replace('{{site_name}}', e($site_name), $html);
    }

    public function counts(array $definition): array
    {
        $count = function ($type, $plugin) use ($definition) {
            return count(array_filter($definition[$type], function ($row) use ($plugin) {
                return $row['plugin'] === $plugin;
            }));
        };
        return [
            '追加ページ' => count($definition['pages']) - 1, '更新ページ' => 1,
            'フレーム' => count($definition['frames']), 'バケツ' => count($definition['buckets']),
            '固定記事' => count($definition['contents']), 'メニュー設定' => count($definition['menus']),
            'ブログ設定' => $count('buckets', 'blogs'), 'カレンダー設定' => $count('buckets', 'calendars'),
            'カレンダーフレーム設定' => $count('frames', 'calendars'),
        ];
    }
}
