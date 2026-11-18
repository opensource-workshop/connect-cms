<?php

namespace App\Services\SiteTemplates;

use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\Core\SiteTemplateApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * 通常Seeder直後の状態だけを許可する。判定対象はdoc/site-templates.mdを参照。
 * 論理削除・履歴・孤立データもQuery Builderで検出する。
 */
class SiteTemplatePreflight
{
    public const EMPTY_TABLES = [
        'bbs_frames',
        'bbs_posts',
        'bbses',
        'blogs',
        'blogs_frames',
        'blogs_posts',
        'blogs_posts_tags',
        'buckets',
        'buckets_mails',
        'buckets_roles',
        'cabinet_contents',
        'cabinets',
        'calendar_frames',
        'calendar_posts',
        'calendars',
        'categories',
        'codes',
        'codes_help_messages',
        'codes_searches',
        'contents',
        'convention_frames',
        'convention_joins',
        'convention_posts',
        'conventions',
        'counter_counts',
        'counter_frames',
        'counters',
        'databases',
        'databases_columns',
        'databases_columns_roles',
        'databases_columns_selects',
        'databases_frames',
        'databases_input_cols',
        'databases_inputs',
        'databases_roles',
        'databases_searched_words',
        'databasesearches',
        'faqs',
        'faqs_posts',
        'faqs_posts_tags',
        'forms',
        'forms_columns',
        'forms_columns_selects',
        'forms_input_cols',
        'forms_inputs',
        'frame_configs',
        'frames',
        'inputs_repeats',
        'learningtasks',
        'learningtasks_configs',
        'learningtasks_examinations',
        'learningtasks_posts',
        'learningtasks_posts_files',
        'learningtasks_use_settings',
        'learningtasks_users',
        'learningtasks_users_statuses',
        'like_users',
        'likes',
        'linklist_frames',
        'linklist_posts',
        'linklists',
        'logins',
        'menus',
        'migration_mappings',
        'numbers',
        'opacs',
        'opacs_books',
        'opacs_books_lents',
        'opacs_configs',
        'opacs_configs_selects',
        'opacs_frames',
        'openingcalendars',
        'openingcalendars_days',
        'openingcalendars_months',
        'openingcalendars_patterns',
        'page_roles',
        'permalinks',
        'photoalbum_contents',
        'photoalbums',
        'plugin_categories',
        'reservations',
        'reservations_choice_categories',
        'reservations_columns_selects',
        'reservations_facilities',
        'reservations_inputs',
        'reservations_inputs_columns',
        'rss_urls',
        'rsses',
        'searchs',
        'searchs_dual',
        'slideshows',
        'slideshows_items',
        'tabs',
        'uploads',
        'whatsnews',
        'whatsnews_dual',
    ];

    public const OPTIONAL_TABLES = [
        'knowledges',
        'codestudies',
        'sampleforms',
        'learningtasks_categories',
        'blogs_categories',
        'faqs_categories',
        'learningtasks_posts_tags',
        'receives',
        'receive_datas',
        'receive_records',
    ];

    private const STANDARD_PLUGINS = [
        'bbses',
        'blogs',
        'cabinets',
        'calendars',
        'contents',
        'conventions',
        'counters',
        'databases',
        'databasesearches',
        'faqs',
        'forms',
        'learningtasks',
        'linklists',
        'logins',
        'menus',
        'opacs',
        'openingcalendars',
        'photoalbums',
        'reservations',
        'rsses',
        'searchs',
        'slideshows',
        'tabs',
        'themechangers',
        'whatsnews',
    ];

    public const ROLE_CONFIGS = ['new_bucket_role_article_post_flag', 'new_bucket_role_reporter_post_flag'];

    public function inspect(array $definition): array
    {
        $reasons = [];
        $required = array_merge(self::EMPTY_TABLES, ['pages', 'configs', 'plugins', 'users', 'users_roles',
            'site_template_applications', 'reservations_categories', 'reservations_columns', 'reservations_columns_sets']);
        foreach ($required as $table) {
            if (!Schema::hasTable($table)) {
                $reasons[] = '必要なテーブルがありません：' . $table;
            }
        }
        if ($reasons) {
            return ['reasons' => $reasons, 'site_reasons' => $reasons, 'template_reasons' => []];
        }
        $columns = [
            'pages' => ['depth', 'layout_inherit_flag', 'override_site_name', 'meta_robots'],
            'frames' => ['page_only', 'display_sequence', 'content_open_type'],
            'contents' => ['status', 'created_id', 'created_name', 'deleted_at'],
            'blogs' => ['use_view_count_spectator', 'narrowing_down_type', 'created_id', 'created_name'],
            'calendars' => ['created_id', 'created_name', 'deleted_at'],
            'calendar_frames' => ['created_id', 'created_name', 'deleted_at'],
            'frame_configs' => ['created_id', 'created_name', 'deleted_at'],
            'site_template_applications' => ['application_key', 'result', 'applied_at'],
        ];
        foreach ($columns as $table => $names) {
            if (!Schema::hasColumns($table, $names)) {
                $reasons[] = '必要なmigrationが未適用です：' . $table;
            }
        }
        if ($reasons) {
            return ['reasons' => $reasons, 'site_reasons' => $reasons, 'template_reasons' => []];
        }
        $occupied = [];
        $present = array_fill_keys($required, true);
        foreach (array_merge(self::EMPTY_TABLES, self::OPTIONAL_TABLES) as $table) {
            if ((isset($present[$table]) || Schema::hasTable($table)) && DB::table($table)->exists()) {
                $occupied[] = $table;
            }
        }
        if ($occupied) {
            $reasons[] = 'ページに追加したプラグインや、作成済みの記事などのデータがあります。';
        }
        $home = Page::first();
        $defaults = [
            'page_name' => 'home', 'permanent_link' => '/', 'base_display_flag' => 1,
            'layout_inherit_flag' => 0, 'depth' => 0, '_lft' => 1, '_rgt' => 2,
            'membership_flag' => 0, 'container_flag' => 0, 'othersite_url_target' => 0,
            'transfer_lower_page_flag' => 0,
        ];
        $nullable = ['parent_id', 'background_color', 'header_color', 'theme', 'layout', 'ip_address',
            'class', 'othersite_url', 'password', 'override_site_name', 'meta_robots'];
        $initial = $home && Page::count() === 1;
        if ($initial) {
            foreach ($defaults as $key => $value) {
                $initial = $initial && (string) $home->$key === (string) $value;
            }
            foreach ($nullable as $key) {
                $initial = $initial && $home->$key === null;
            }
        }
        if (!$initial) {
            $reasons[] = 'ページ構成またはトップページが初期状態から変更されています。';
        }
        if (SiteTemplateApplication::whereNotNull('applied_at')->exists()) {
            $reasons[] = 'サイトひな型は適用済みです。';
        }
        $site_reasons = $reasons;
        $template_reasons = [];
        $unavailable_plugins = [];
        $restricted_plugins = [];
        $plugins = DB::table('plugins')->orderBy('id')->get();
        foreach ($definition['required_plugins'] as $plugin) {
            $label = SiteTemplateDefinition::PLUGIN_LABELS[$plugin];
            if (in_array($plugin, config('connect.PLUGIN_FORCE_HIDDEN', []), true)) {
                $restricted_plugins[] = $label;
            } elseif (!$plugins->contains(function ($row) use ($plugin) {
                return strtolower($row->plugin_name) === $plugin && (int) $row->display_flag === 1;
            })) {
                $unavailable_plugins[] = $label;
            }
        }
        if ($unavailable_plugins) {
            $template_reasons[] = '必要なプラグインが利用できません。プラグイン管理で対象プラグインの「表示」をONにしてください。（対象: ' . implode('、', $unavailable_plugins) . '）';
        }
        if ($restricted_plugins) {
            $template_reasons[] = '必要なプラグインはシステム設定で利用が制限されています。プラグイン管理で利用状況を確認し、利用制限の設定をシステム管理者に確認してください。（対象: ' . implode('、', $restricted_plugins) . '）';
        }
        foreach ($plugins as $plugin) {
            $name = $plugin->plugin_name;
            if (!in_array(strtolower($name), self::STANDARD_PLUGINS, true) ||
                !is_file(app_path('Plugins/User/' . $name . '/' . $name . 'Plugin.php'))) {
                $site_reasons[] = '追加プラグインの初期状態は判定できません：' . $name;
            }
        }
        foreach ($definition['frames'] as $frame) {
            $entry = $frame['plugin'] === 'calendars' ? 'index' : $frame['plugin'];
            if (!is_file(resource_path('views/plugins/user/' . $frame['plugin'] . '/' . $frame['template'] . '/' . $entry . '.blade.php'))) {
                $template_reasons[] = '表示テンプレートがありません：' . $frame['plugin'] . '/' . $frame['template'];
            }
        }
        $reservation_state = $this->reservationDefaults();
        if (!$reservation_state) {
            $site_reasons[] = '施設予約の項目またはカテゴリが初期状態から変更されています。';
        }
        if (DB::getDriverName() !== 'mysql') {
            $site_reasons[] = 'サイトひな型にはトランザクション対応のMySQLが必要です。';
        } elseif (DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', $required)->where('ENGINE', '<>', 'InnoDB')->exists()) {
            $site_reasons[] = 'トランザクションに対応しないテーブルがあります。';
        }
        $configs = Configs::whereIn('name', array_merge(self::ROLE_CONFIGS, ['base_site_name']))->orderBy('name')->get();
        $reasons = array_merge($site_reasons, $template_reasons);
        $state = [$home ? $home->getAttributes() : null, $occupied, $reasons,
            $plugins->map(function ($p) {
                return [$p->plugin_name, $p->display_flag];
            })->all(),
            $configs->map(function ($c) {
                return [$c->name, $c->value];
            })->all(), $reservation_state];
        return [
            'reasons' => array_values(array_unique($reasons)), 'home' => $home,
            'site_reasons' => array_values(array_unique($site_reasons)),
            'template_reasons' => array_values(array_unique($template_reasons)),
            'configs' => $configs->whereIn('name', self::ROLE_CONFIGS)->values(),
            'site_name' => Configs::getConfigsValue($configs, 'base_site_name', ''),
            'state_checksum' => hash('sha256', json_encode($state)),
        ];
    }

    /**
     * 施設予約の標準データだけを許可する。IDと監査日時には依存しない。
     */
    private function reservationDefaults(): bool
    {
        $categories = DB::table('reservations_categories')->get();
        $sets = DB::table('reservations_columns_sets')->get();
        if ($categories->count() !== 1 || $sets->count() !== 1 ||
            $categories[0]->category !== 'カテゴリなし' || (int) $categories[0]->display_sequence !== 1 ||
            $sets[0]->name !== '基本' || (int) $sets[0]->display_sequence !== 1) {
            return false;
        }
        $columns = DB::table('reservations_columns')->orderBy('display_sequence')->get();
        $expected = [
            ['件名', \App\Enums\ReservationColumnType::text, 1, 1],
            ['登録者', \App\Enums\ReservationColumnType::created_name, 0, 0],
            ['更新日', \App\Enums\ReservationColumnType::updated, 0, 0],
        ];
        if ($columns->count() !== 3) {
            return false;
        }
        foreach ($expected as $index => $values) {
            $column = $columns[$index];
            if ((int) $column->columns_set_id !== (int) $sets[0]->id || $column->column_name !== $values[0] ||
                (string) $column->column_type !== (string) $values[1] || (int) $column->required !== $values[2] ||
                (int) $column->title_flag !== $values[3] || (string) $column->hide_flag !== '0' ||
                (int) $column->display_sequence !== $index + 1 || isset($column->deleted_at)) {
                return false;
            }
        }
        return !isset($categories[0]->deleted_at) && !isset($sets[0]->deleted_at);
    }

    /**
     * 履歴予約自体もロールバックできるよう、書込先のエンジンは予約前に検査する。
     */
    public function assertStorageReady(): void
    {
        $tables = ['site_template_applications', 'pages', 'frames', 'buckets', 'buckets_roles',
            'blogs', 'calendars', 'calendar_frames', 'frame_configs', 'menus', 'contents', 'configs'];
        if (DB::getDriverName() !== 'mysql') {
            throw new InvalidArgumentException('サイトひな型にはトランザクション対応のMySQLが必要です。');
        }
        $engines = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', $tables)->pluck('ENGINE');
        if ($engines->count() !== count($tables) || $engines->contains(function ($engine) {
            return $engine !== 'InnoDB';
        })) {
            throw new InvalidArgumentException('必要なテーブルがないか、トランザクションに対応していません。');
        }
    }

    public function assertReady(array $state): void
    {
        if ($state['reasons']) {
            throw new InvalidArgumentException(implode(' ', $state['reasons']));
        }
    }
}
