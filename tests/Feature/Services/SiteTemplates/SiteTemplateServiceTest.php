<?php

namespace Tests\Feature\Services\SiteTemplates;

use App\Models\Common\Buckets;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\User\Contents\Contents;
use App\Services\SiteTemplates\SiteTemplateDefinition;
use App\Services\SiteTemplates\SiteTemplatePreflight;
use App\Services\SiteTemplates\SiteTemplateService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * 初期Seederからの生成・残存データ拒否・設定引継ぎ・障害時の原子性を確認する。
 */
class SiteTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $actor;
    protected $service;
    protected $input = ['template_id' => 'school-basic', 'site_name' => 'テスト学校 & 学園'];

    /** 専用DBで標準インストール直後の状態を用意する。 */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        \App\Models\Core\UsersRoles::updateOrCreate([
            'users_id' => User::first()->id, 'target' => 'manage', 'role_name' => 'admin_system',
        ], ['role_value' => 1]);
        $this->actor = User::first();
        $this->service = app(SiteTemplateService::class);
    }

    /** 件数、トップID、階層、関連、公開状態、監査情報と既存設定の保持を保証する。 */
    public function testGeneration(): void
    {
        DB::table('groups')->insert(['name' => '既存グループ']);
        $preserved = [];
        foreach (['users', 'users_roles', 'groups', 'group_users', 'users_columns', 'users_columns_sets'] as $table) {
            $preserved[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }
        $this->assertFalse(Schema::hasColumn('site_template_applications', 'template_version'));
        $home = Page::first()->id;
        $configs = DB::table('configs')->where('name', '<>', 'base_site_name')->get()->toJson();
        $reservations = DB::table('reservations_columns')->get()->toJson();
        $application = $this->service->apply($this->actor, $this->service->plan($this->actor, $this->input));
        $this->assertSame($home, $application->result['pages']['home']);
        $definition = app(SiteTemplateDefinition::class)->load('school-basic');
        foreach ($definition['contents'] as $content) {
            $this->assertDatabaseHas('contents', [
                'bucket_id' => $application->result['buckets'][$content['bucket']],
                'content_text' => app(SiteTemplateDefinition::class)->renderHtml($content['html'], $this->input['site_name']),
            ]);
        }
        foreach (['pages' => 10, 'frames' => 17, 'buckets' => 12, 'contents' => 9, 'menus' => 2,
            'blogs' => 2, 'calendars' => 1, 'calendar_frames' => 2, 'frame_configs' => 4] as $table => $count) {
            $this->assertDatabaseCount($table, $count);
        }
        // メインエリアは元からページ内のみ。通常配置と同じ既定値にする。
        $this->assertSame(0, DB::table('frames')->where('page_only', '<>', 0)->count());
        $this->assertFalse(Page::isBroken());
        $pages = Page::defaultOrder()->get();
        $this->assertSame(['/', '/about', '/about/greeting', '/about/policy', '/about/access', '/news', '/blog', '/calendar', '/letters', '/disaster'], $pages->pluck('permanent_link')->all());
        $this->assertSame([0, 0, 1, 1, 1, 0, 0, 0, 0, 0], $pages->pluck('depth')->map(function ($v) {
            return (int) $v;
        })->all());
        foreach ($pages as $page) {
            $this->assertSame(1, (int) $page->layout_inherit_flag);
            $this->assertSame($page->id === $home ? '1|1|0|1' : null, $page->layout);
        }
        $this->assertSame(0, Contents::where('created_id', '<>', $this->actor->id)->count());
        $this->assertSame(9, Contents::where('status', \App\Enums\StatusType::active)->count());
        $this->assertStringContainsString('テスト学校 &amp; 学園', Contents::pluck('content_text')->implode(''));
        $this->assertSame($configs, DB::table('configs')->where('name', '<>', 'base_site_name')->get()->toJson());
        $this->assertSame($reservations, DB::table('reservations_columns')->get()->toJson());
        foreach ($preserved as $table => $rows) {
            $this->assertSame($rows, DB::table($table)->orderBy('id')->get()->toJson(), $table);
        }
        $this->assertSame($this->actor->id, $application->created_id);
        $this->expectException(InvalidArgumentException::class);
        $this->service->plan($this->actor, $this->input);
    }

    /** 初期ホームのIDが1以外でも再利用する。 */
    public function testNonDefaultHomeId(): void
    {
        DB::table('pages')->update(['id' => 123]);
        $application = $this->service->apply($this->actor, $this->service->plan($this->actor, $this->input));
        $this->assertSame(123, $application->result['pages']['home']);
    }

    /** @dataProvider postRoleSettings
     * DB値を共有設定より優先し、未設定時も全バケツで同じ投稿権限を使う。
     */
    public function testConfigSnapshot(?int $article, ?int $reporter): void
    {
        foreach (['article' => $article, 'reporter' => $reporter] as $role => $flag) {
            if ($flag !== null) {
                Configs::updateOrCreate(['name' => 'new_bucket_role_' . $role . '_post_flag'], ['category' => 'general', 'value' => $flag]);
            }
        }
        app(Request::class)->attributes->set('configs', collect([
            new Configs(['name' => 'new_bucket_role_article_post_flag', 'value' => $article ? '0' : '1']),
            new Configs(['name' => 'new_bucket_role_reporter_post_flag', 'value' => $reporter ? '0' : '1']),
        ]));
        $this->service->apply($this->actor, $this->service->plan($this->actor, $this->input));
        $this->assertDatabaseCount('buckets_roles', 12 * ($article + $reporter));
        $this->assertSame(12 * (int) $article, DB::table('buckets_roles')->where('role', 'role_article')->where('post_flag', 1)->count());
        $this->assertSame(12 * (int) $reporter, DB::table('buckets_roles')->where('role', 'role_reporter')->where('post_flag', 1)->count());
    }

    /** ON/OFFとDB未登録の組合せ。 */
    public function postRoleSettings(): array
    {
        return [[0, 0], [0, 1], [1, 0], [1, 1], [null, null]];
    }

    /** 明示的な空設定は共有設定へ戻らず、既存の引数なし呼出しは共有設定を使用する。 */
    public function testEmptyExplicitConfigs(): void
    {
        app(Request::class)->attributes->set('configs', collect([
            new Configs(['name' => 'new_bucket_role_article_post_flag', 'value' => '1']),
        ]));
        Buckets::createWithDefaultPostRoles(['bucket_name' => '空設定', 'plugin_name' => 'contents'], collect());
        $this->assertDatabaseCount('buckets_roles', 0);
        Buckets::createWithDefaultPostRoles(['bucket_name' => '従来動作', 'plugin_name' => 'contents']);
        $this->assertDatabaseCount('buckets_roles', 1);
    }

    /** 確認後に設定が変われば予約記録を含めて更新せず再確認を求める。 */
    public function testChangedConfig(): void
    {
        $plan = $this->service->plan($this->actor, $this->input);
        Configs::updateOrCreate(['name' => 'new_bucket_role_reporter_post_flag'], ['category' => 'general', 'value' => '1']);
        try {
            $this->service->apply($this->actor, $plan);
            $this->fail('変更後の適用を拒否する必要があります');
        } catch (InvalidArgumentException $exception) {
            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        }
        $this->assertDatabaseCount('site_template_applications', 0);
        $this->assertDatabaseCount('pages', 1);
    }

    /** 途中のモデル保存で失敗しても全体を戻し、再実行できる。 */
    public function testRollback(): void
    {
        $plan = $this->service->plan($this->actor, $this->input);
        $fail = true;
        Contents::creating(function () use (&$fail) {
            if ($fail) {
                throw new \RuntimeException('test failure');
            }
        });
        try {
            $this->service->apply($this->actor, $plan);
            $this->fail('保存途中で例外が発生する必要があります');
        } catch (\RuntimeException $exception) {
            $this->assertSame('test failure', $exception->getMessage());
        }
        foreach (['frames', 'buckets', 'menus', 'blogs', 'calendars', 'calendar_frames', 'frame_configs', 'site_template_applications'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertSame('home', Page::first()->page_name);
        $this->assertNull(Page::first()->layout);
        $fail = false;
        $this->service->apply($this->actor, $this->service->plan($this->actor, $this->input));
        $this->assertDatabaseCount('site_template_applications', 1);
    }

    /** 全拒否対象テーブルの残存レコードを論理削除や参照先に依存せず検知する。 */
    public function testAllResidualTables(): void
    {
        $definition = app(SiteTemplateDefinition::class)->load('school-basic');
        foreach (array_merge(SiteTemplatePreflight::EMPTY_TABLES, SiteTemplatePreflight::OPTIONAL_TABLES) as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            DB::statement("SET SESSION sql_mode = ''");
            Schema::disableForeignKeyConstraints();
            DB::table($table)->insert(['id' => 900001]);
            Schema::enableForeignKeyConstraints();
            $state = app(SiteTemplatePreflight::class)->inspect($definition);
            $this->assertNotEmpty($state['site_reasons'], $table);
            DB::table($table)->where('id', 900001)->delete();
        }
    }

    /** @dataProvider changedHome
     * 初期ページの表示・公開・階層の変更は初期状態と扱わない。
     */
    public function testExistingStates(array $values): void
    {
        DB::table('pages')->update($values);
        $this->expectException(InvalidArgumentException::class);
        $this->service->plan($this->actor, $this->input);
    }

    /** 初期トップの変更例。 */
    public function changedHome(): array
    {
        return [[['page_name' => '変更']], [['layout' => '0|0|0|0']], [['membership_flag' => 1]], [['password' => 'secret']], [['depth' => 1]]];
    }
    /** @dataProvider unavailableStates
     * DB準備不足、利用不能なプラグイン、標準予約定義の変更を理由付きで拒否する。
     */
    public function testUnavailableState(string $case): void
    {
        $definition = app(SiteTemplateDefinition::class)->load('school-basic');
        switch ($case) {
            case 'hidden':
                config(['connect.PLUGIN_FORCE_HIDDEN' => ['blogs']]);
                break;
            case 'disabled':
                DB::table('plugins')->where('plugin_name', 'Blogs')->update(['display_flag' => 0]);
                break;
            case 'missing-plugin':
                DB::table('plugins')->where('plugin_name', 'Calendars')->delete();
                break;
            case 'extra-plugin':
                DB::table('plugins')->insert(['plugin_name' => 'Custom', 'plugin_name_full' => '追加', 'display_flag' => 1]);
                break;
            case 'template':
                $definition['frames'][0]['template'] = 'missing';
                break;
            case 'category':
                DB::table('reservations_categories')->update(['category' => '変更']);
                break;
            case 'column':
                DB::table('reservations_columns')->where('display_sequence', 1)->update(['required' => 0]);
                break;
            case 'history':
                DB::table('contents')->insert(['bucket_id' => 987, 'status' => 0, 'deleted_at' => now()]);
                break;
            case 'upload':
                DB::table('uploads')->insert(['client_original_name' => 'residual.txt', 'size' => 1]);
                break;
            case 'page':
                (new Page(['page_name' => '追加', 'permanent_link' => '/extra']))->saveAsRoot();
                break;
        }
        $state = app(SiteTemplatePreflight::class)->inspect($definition);
        $this->assertNotEmpty($state['reasons'], $case);
        $this->assertDatabaseCount('site_template_applications', 0);
    }

    /** 適用できない初期状態の代表例。 */
    public function unavailableStates(): array
    {
        return array_map(function ($v) {
            return [$v];
        }, [
            'hidden', 'disabled', 'missing-plugin', 'extra-plugin', 'template', 'category', 'column', 'history', 'upload', 'page',
        ]);
    }
}
