<?php

namespace Tests\Feature\Plugins\Manage\SiteManage;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\Core\SiteTemplateApplication;
use App\Models\Core\UsersRoles;
use App\Services\SiteTemplates\SiteTemplateService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * サイトひな型の確認と実行を実際のHTTP経路から検証する。
 * 管理プラグインのrequireによる再宣言を避けるため、管理リクエストは各テストで一回にする。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SiteTemplateHttpTest extends TestCase
{
    use RefreshDatabase;

    private $input = ['template_id' => 'school-basic', 'site_name' => 'サイトひな型テスト学校'];

    /** 標準の初期状態から管理者で操作する。 */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        UsersRoles::updateOrCreate([
            'users_id' => User::first()->id, 'target' => 'manage', 'role_name' => 'admin_system',
        ], ['role_value' => 1]);
        $this->actingAs(User::first());
    }

    /** 確認POSTは実行者に結び付く計画を保存し、サイトデータを作らない。 */
    public function testConfirmationCreatesPlanWithoutChangingSite(): void
    {
        $this->post('/manage/site/confirmSiteTemplate', $this->input)->assertOk()
            ->assertViewIs('plugins.manage.site.site_templates_confirm')
            ->assertViewHas('plan', function ($plan) {
                return $plan['input'] === $this->input;
            });
        $confirmation = session('site_template_confirmation');
        $this->assertSame(User::first()->id, $confirmation['plan']['actor_id']);
        $this->assertSame($this->input, $confirmation['plan']['input']);
        $this->assertNotEmpty($confirmation['token']);
        $this->assertGreaterThan(time(), $confirmation['expires_at']);
        $this->assertDatabaseCount('pages', 1);
        $this->assertDatabaseCount('site_template_applications', 0);
    }

    /** 確認済みの適用POSTでサイトを生成し、確認を消費して結果へ遷移する。 */
    public function testApplyConfirmedTemplate(): void
    {
        $confirmation = $this->prepareConfirmation();
        $response = $this->post('/manage/site/applySiteTemplate', [
            'confirmation_token' => $confirmation['token'], 'confirmed' => '1',
        ]);
        $application = SiteTemplateApplication::firstOrFail();
        $response->assertRedirect('/manage/site/siteTemplateResult/' . $application->id);
        $this->assertNull(session('site_template_confirmation'));
        $this->assertSame('web', $application->applied_via);
        $this->assertDatabaseCount('pages', 10);
        $this->assertDatabaseCount('site_template_applications', 1);
        $this->assertDatabaseHas('configs', ['name' => 'base_site_name', 'value' => $this->input['site_name']]);
    }

    /** 適用後の結果画面へ、適用記録と定義の表示名を渡す。 */
    public function testResultShowsAppliedTemplate(): void
    {
        $application = $this->applyTemplate();
        $definition = app(SiteTemplateService::class)->templates()[0];
        $this->get('/manage/site/siteTemplateResult/' . $application->id)->assertOk()
            ->assertViewIs('plugins.manage.site.site_templates_result')
            ->assertViewHas('application', function ($actual) use ($application) {
                return $actual->id === $application->id;
            })->assertViewHas('template_name', $definition['name']);
    }

    /** 異なる基本レイアウトでも、別の最上位ページと子ページに共通フレームを表示する。 */
    public function testInheritedAreas(): void
    {
        Configs::updateOrCreate(['name' => 'base_layout'], ['category' => 'general', 'value' => '0|0|0|0']);
        $this->applyTemplate();
        foreach (['/about', '/about/greeting', '/news', '/calendar'] as $url) {
            $response = $this->get($url)->assertOk()->assertSee($this->input['site_name']);
            foreach (Frame::where('area_id', '<>', \App\Enums\AreaType::main)->pluck('id') as $frameId) {
                $response->assertSee('id="frame-' . $frameId . '"', false);
            }
        }
        $this->assertDatabaseHas('configs', ['name' => 'base_layout', 'value' => '0|0|0|0']);
    }

    /** 使用済みの計画を再送信しても生成物を追加しない。 */
    public function testRepeatedApplicationIsRejected(): void
    {
        $confirmation = $this->prepareConfirmation();
        $this->applyTemplate();
        $this->post('/manage/site/applySiteTemplate', [
            'confirmation_token' => $confirmation['token'], 'confirmed' => '1',
        ])->assertSessionHasErrors();
        $this->assertDatabaseCount('site_template_applications', 1);
        $this->assertDatabaseCount('pages', 10);
        $this->assertDatabaseCount('frames', 17);
    }

    /** @dataProvider invalidSiteNames
     * HTMLを含むサイト名は拒否し、入力値を再表示用に保存する。
     */
    public function testInvalidSiteNameIsRejected(string $site_name): void
    {
        $this->post('/manage/site/confirmSiteTemplate', array_merge($this->input, ['site_name' => $site_name]))
            ->assertRedirect('/manage/site/siteTemplates')->assertSessionHasErrors();
        $this->assertSame($site_name, session()->getOldInput('site_name'));
        $this->assertSame($this->input['template_id'], session()->getOldInput('template_id'));
        $this->assertDatabaseCount('pages', 1);
    }

    /** 拒否するHTML入力。 */
    public function invalidSiteNames(): array
    {
        return [['<学校>'], ['<script>alert(1)</script>']];
    }

    /** 再表示用の入力をエスケープし、選択したサイトひな型を復元する。 */
    public function testSelectionRestoresOldInput(): void
    {
        $this->withSession(['_old_input' => array_merge($this->input, ['site_name' => '<学校>'])]);
        $response = $this->get('/manage/site/siteTemplates')->assertOk()->assertSee('value="&lt;学校&gt;"', false);
        $this->assertMatchesRegularExpression('/value="school-basic"[^>]*checked/', $response->getContent());
        $this->assertMatchesRegularExpression('/id="site-template-detail-0" class="collapse\s+show\s*"/', $response->getContent());
        $this->assertDatabaseCount('pages', 1);
    }

    /** 一覧はラジオ選択と共通の入力欄を持ち、初期状態では未選択・サイト名未入力にする。 */
    public function testTemplateSelectionList(): void
    {
        $response = $this->get('/manage/site/siteTemplates')->assertOk()->assertViewIs('plugins.manage.site.site_templates');
        $response->assertSee('type="radio"', false)->assertSee('name="template_id" value="school-basic"', false);
        $response->assertSee('data-parent="#site-template-list"', false);
        $response->assertDontSee('type="hidden" name="template_id"', false);
        $this->assertMatchesRegularExpression('/id="site-template-detail-0" class="collapse\s*"/', $response->getContent());
        $this->assertSame(1, substr_count($response->getContent(), 'name="site_name"'));
        $this->assertMatchesRegularExpression('/name="site_name"[^>]*value=""/', $response->getContent());
    }

    /** サイトひな型を選択していない確認POSTを拒否する。 */
    public function testTemplateSelectionIsRequired(): void
    {
        $this->post('/manage/site/confirmSiteTemplate', ['site_name' => $this->input['site_name']])
            ->assertRedirect('/manage/site/siteTemplates')->assertSessionHasErrors();
        $this->assertDatabaseCount('pages', 1);
        $this->assertDatabaseCount('site_template_applications', 0);
    }

    /** 複数候補でも同じラジオグループと候補ごとの詳細を表示し、入力欄は共通にする。 */
    public function testMultipleTemplateChoices(): void
    {
        $templates = app(SiteTemplateService::class)->templates();
        $other = $templates[0];
        $other['id'] = 'other-school';
        $other['name'] = '別のサイトひな型';
        $this->partialMock(SiteTemplateService::class, function ($mock) use ($templates, $other) {
            $mock->shouldReceive('templates')->once()->andReturn([$templates[0], $other]);
        });
        $response = $this->get('/manage/site/siteTemplates')->assertOk()->assertViewHas('templates', [$templates[0], $other]);
        $response->assertSee('id="site-template-detail-1"', false);
        $this->assertSame(2, substr_count($response->getContent(), 'name="template_id" value='));
        $this->assertSame(1, substr_count($response->getContent(), 'name="site_name"'));
    }

    /** 既存プラグインがある場合は画面の適用操作を表示しない。 */
    public function testExistingPluginsPreventSelection(): void
    {
        $this->createExistingFrame();
        $this->get('/manage/site/siteTemplates')->assertOk()
            ->assertViewHas('state', function ($state) {
                return !empty($state['site_reasons']);
            })->assertDontSee('type="submit"', false);
    }

    /** 既存プラグインがある場合は直接の確認POSTも拒否する。 */
    public function testExistingPluginsPreventConfirmation(): void
    {
        $this->createExistingFrame();
        $this->post('/manage/site/confirmSiteTemplate', $this->input)->assertSessionHasErrors();
        $this->assertDatabaseCount('site_template_applications', 0);
    }

    /** 利用できないプラグインを候補の可否として扱う。 */
    public function testUnavailablePluginsAffectSelection(): void
    {
        $this->disableBlogAndCalendar();
        $this->get('/manage/site/siteTemplates')->assertOk()
            ->assertViewHas('template_states', function ($states) {
                return empty($states['school-basic']['site_reasons']) && count($states['school-basic']['template_reasons']) === 1;
            })->assertSee('value="school-basic" data-template-available="0"', false);
    }

    /** 必要プラグインが利用できない確認POSTを拒否する。 */
    public function testUnavailablePluginsPreventConfirmation(): void
    {
        $this->disableBlogAndCalendar();
        $this->post('/manage/site/confirmSiteTemplate', $this->input)
            ->assertRedirect('/manage/site/siteTemplates')->assertSessionHasErrors();
        $this->assertDatabaseCount('pages', 1);
    }

    /** システム設定による利用制限を候補の可否として扱う。 */
    public function testRestrictedPluginsAffectSelection(): void
    {
        config(['connect.PLUGIN_FORCE_HIDDEN' => ['blogs']]);
        $this->get('/manage/site/siteTemplates')->assertOk()
            ->assertViewHas('template_states', function ($states) {
                return empty($states['school-basic']['site_reasons']) && !empty($states['school-basic']['template_reasons']);
            });
    }

    /** システム設定で利用が制限されている確認POSTを拒否する。 */
    public function testRestrictedPluginsPreventConfirmation(): void
    {
        config(['connect.PLUGIN_FORCE_HIDDEN' => ['blogs']]);
        $this->post('/manage/site/confirmSiteTemplate', $this->input)->assertSessionHasErrors();
        $this->assertDatabaseCount('site_template_applications', 0);
    }

    /** 候補ごとの必要プラグインを調べ、他の候補の利用不能状態を引き継がない。 */
    public function testTemplateSpecificAvailability(): void
    {
        $this->disableBlogAndCalendar();
        $templates = app(SiteTemplateService::class)->templates();
        $other = $templates[0];
        $other['id'] = 'simple-school';
        $other['name'] = '固定記事中心のサイトひな型';
        $other['required_plugins'] = ['contents', 'menus'];
        foreach (['frames', 'buckets'] as $type) {
            $other[$type] = array_values(array_filter($other[$type], function ($row) {
                return in_array($row['plugin'], ['contents', 'menus'], true);
            }));
        }
        $this->partialMock(SiteTemplateService::class, function ($mock) use ($templates, $other) {
            $mock->shouldReceive('templates')->once()->andReturn([$templates[0], $other]);
        });
        $this->get('/manage/site/siteTemplates')->assertOk()
            ->assertSee('value="school-basic" data-template-available="0"', false)
            ->assertSee('value="simple-school" data-template-available="1"', false)
            ->assertDontSee('data-template-warning="simple-school"', false);
    }

    /** @dataProvider invalidConfirmations
     * 確認なし、期限切れ、トークン・実行者・入力・状態の改変を拒否する。
     */
    public function testInvalidConfirmation(string $case): void
    {
        $confirmation = $this->prepareConfirmation();
        $payload = ['confirmation_token' => $confirmation['token'], 'confirmed' => '1'];
        switch ($case) {
            case 'missing':
                $this->app['session']->forget('site_template_confirmation');
                break;
            case 'expired':
                $confirmation['expires_at'] = time() - 1;
                $this->withSession(['site_template_confirmation' => $confirmation]);
                break;
            case 'token':
                $payload['confirmation_token'] = 'wrong';
                break;
            case 'unchecked':
                unset($payload['confirmed']);
                break;
            case 'actor':
                $confirmation['plan']['actor_id'] = 987;
                $this->withSession(['site_template_confirmation' => $confirmation]);
                break;
            case 'input':
                $payload['site_name'] = '変更後';
                break;
            case 'definition':
                $confirmation['plan']['template_checksum'] = str_repeat('0', 64);
                $this->withSession(['site_template_confirmation' => $confirmation]);
                break;
            case 'state':
                Configs::where('name', 'base_site_name')->update(['value' => '変更後']);
                break;
        }
        $this->post('/manage/site/applySiteTemplate', $payload)->assertRedirect('/manage/site/siteTemplates')->assertSessionHasErrors();
        $this->assertDatabaseCount('pages', 1);
        $this->assertDatabaseCount('site_template_applications', 0);
    }

    /** 確認の拒否条件。 */
    public function invalidConfirmations(): array
    {
        return array_map(function ($v) {
            return [$v];
        }, ['missing', 'expired', 'token', 'unchecked', 'actor', 'input', 'definition', 'state']);
    }

    /** @dataProvider unauthorizedRequests
     * システム管理者以外は各管理アクションへの直接アクセスもできない。
     */
    public function testUnauthorized(string $role, string $method, string $path): void
    {
        $this->app['auth']->forgetGuards();
        if ($role !== 'guest') {
            $user = User::factory()->create();
            if ($role !== 'general') {
                UsersRoles::factory()->create(['users_id' => $user->id, 'target' => 'manage', 'role_name' => $role, 'role_value' => 1]);
            }
            $this->actingAs($user);
        }
        $this->call($method, $path, $method === 'POST' ? $this->input : [])->assertForbidden();
        $this->assertDatabaseCount('pages', 1);
    }

    /** 権限不足の利用者と保護する管理アクション。 */
    public function unauthorizedRequests(): array
    {
        $requests = [];
        foreach (['guest', 'general', 'admin_site', 'admin_page'] as $role) {
            foreach ([
                ['GET', '/manage/site/siteTemplates'],
                ['POST', '/manage/site/confirmSiteTemplate'],
                ['POST', '/manage/site/applySiteTemplate'],
                ['GET', '/manage/site/siteTemplateResult/1'],
            ] as $request) {
                $requests[] = [$role, $request[0], $request[1]];
            }
        }
        return $requests;
    }

    /** @dataProvider updateActions
     * 更新アクションのGETを拒否する。
     */
    public function testUpdateActionsRejectGet(string $action): void
    {
        $this->get('/manage/site/' . $action)->assertStatus(405);
        $this->assertDatabaseCount('site_template_applications', 0);
    }

    /** @dataProvider updateActions
     * 実際のCSRFミドルウェアでトークンのないPOSTを拒否する。
     */
    public function testCsrfIsRequired(string $action): void
    {
        $this->app->bind(VerifyCsrfToken::class, function ($app) {
            return new class($app, $app['encrypter']) extends VerifyCsrfToken {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
        $this->post('/manage/site/' . $action, $this->input)->assertRedirect()->assertSessionHas('flash_message_for_header');
        $this->assertNull(session('site_template_confirmation'));
        $this->assertDatabaseCount('site_template_applications', 0);
    }

    /** POSTだけを許可するアクション。 */
    public function updateActions(): array
    {
        return [['applySiteTemplate'], ['confirmSiteTemplate']];
    }

    /** @dataProvider subdirectoryActions
     * サブディレクトリ設置で各段階のフォーム・遷移・結果リンクを生成する。
     */
    public function testSubdirectoryLinks(string $action): void
    {
        $this->useSubdirectory();
        if ($action === 'selection') {
            $this->get('/manage/site/siteTemplates')->assertOk()->assertSee('http://localhost/cms/manage/site/confirmSiteTemplate', false);
        } elseif ($action === 'confirmation') {
            $this->post('/manage/site/confirmSiteTemplate', $this->input)->assertOk()->assertSee('http://localhost/cms/manage/site/applySiteTemplate', false);
        } elseif ($action === 'application') {
            $confirmation = $this->prepareConfirmation();
            $response = $this->post('/manage/site/applySiteTemplate', ['confirmation_token' => $confirmation['token'], 'confirmed' => '1']);
            $response->assertRedirect();
            $this->assertStringStartsWith('http://localhost/cms/manage/site/siteTemplateResult/', $response->headers->get('Location'));
        } else {
            $application = $this->applyTemplate();
            $this->get('/manage/site/siteTemplateResult/' . $application->id)->assertOk()->assertSee('http://localhost/cms/manage/page', false);
        }
    }

    /** サブディレクトリで確認する画面。 */
    public function subdirectoryActions(): array
    {
        return [['selection'], ['confirmation'], ['application'], ['result']];
    }

    /** 生成した公開ページをサブディレクトリのHTTP経路から表示できる。 */
    public function testPublicPageInSubdirectory(): void
    {
        $this->useSubdirectory();
        $this->applyTemplate();
        $this->get('/about')->assertOk()->assertSee($this->input['site_name']);
    }

    /** 生成データを既存の編集画面から更新し、共有ブログ・予定も両方のページに表示する。 */
    public function testNormalEditing(): void
    {
        config(['app.debug' => true]);
        $this->prepareEditingActor();
        $result = $this->applyTemplate()->result;
        $home = $result['pages']['home'];
        $frames = Frame::get();
        $contentFrame = $frames->firstWhere('plugin_name', 'contents');
        $content = \App\Models\User\Contents\Contents::where('bucket_id', $contentFrame->bucket_id)->first();
        $this->get('/plugin/contents/edit/' . $home . '/' . $contentFrame->id . '/' . $content->id)->assertOk();
        $this->post('/redirect/plugin/contents/update/' . $home . '/' . $contentFrame->id . '/' . $content->id, [
            'contents' => '<p>編集した学校名</p>', 'bucket_name' => '学校名',
        ])->assertRedirect();
        $this->get('/')->assertOk()->assertSee('編集した学校名');

        $menuFrame = $frames->firstWhere('plugin_name', 'menus');
        $this->get('/plugin/menus/select/' . $home . '/' . $menuFrame->id)->assertOk();
        $this->post('/plugin/menus/saveSelect/' . $home . '/' . $menuFrame->id, [
            'select_flag' => 1, 'page_select' => [$home, $result['pages']['about']],
            'folder_close_font' => 0, 'folder_open_font' => 0, 'indent_font' => 0,
        ])->assertOk();
        $this->assertDatabaseHas('menus', ['frame_id' => $menuFrame->id, 'page_ids' => $home . ',' . $result['pages']['about']]);

        $blogFrame = $frames->where('page_id', $result['pages']['blog'])->firstWhere('plugin_name', 'blogs');
        $blog = \App\Models\User\Blogs\Blogs::where('bucket_id', $blogFrame->bucket_id)->first();
        $this->get('/plugin/blogs/create/' . $blogFrame->page_id . '/' . $blogFrame->id)->assertOk();
        $this->post('/redirect/plugin/blogs/save/' . $blogFrame->page_id . '/' . $blogFrame->id, [
            'important' => 0, 'blogs_id' => $blog->id, 'post_title' => '編集確認ブログ', 'post_text' => '<p>本文</p>', 'posted_at' => date('Y-m-d H:i'),
        ])->assertRedirect();
        $this->get('/blog')->assertOk()->assertSee('編集確認ブログ');
        $this->get('/')->assertOk()->assertSee('編集確認ブログ');

        $calendarFrame = $frames->where('page_id', $result['pages']['calendar'])->firstWhere('plugin_name', 'calendars');
        $this->get('/plugin/calendars/edit/' . $calendarFrame->page_id . '/' . $calendarFrame->id)->assertOk();
        $this->post('/redirect/plugin/calendars/save/' . $calendarFrame->page_id . '/' . $calendarFrame->id, [
            'title' => '編集確認予定', 'start_date' => date('Y-m-d'), 'start_time' => '09:00', 'end_time' => '10:00',
        ])->assertRedirect();
        $this->assertDatabaseHas('calendar_posts', ['title' => '編集確認予定']);
        $this->get('/calendar')->assertOk()->assertSee('編集確認予定');
        $this->get('/')->assertOk()->assertSee('編集確認予定');
    }

    /** 生成ページを通常のページ編集画面で開くことができる。 */
    public function testPageEditingScreen(): void
    {
        $this->prepareEditingActor();
        $application = $this->applyTemplate();
        $this->get('/manage/page/edit/' . $application->result['pages']['about'])->assertOk();
    }

    /** 生成ページの名前を通常の管理POSTから変更できる。 */
    public function testPageNameCanBeUpdated(): void
    {
        $this->prepareEditingActor();
        $application = $this->applyTemplate();
        $page_id = $application->result['pages']['about'];
        $this->post('/manage/page/update/' . $page_id, [
            'page_name' => '学校紹介を編集', 'permanent_link' => '/about', 'base_display_flag' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('pages', ['id' => $page_id, 'page_name' => '学校紹介を編集']);
    }

    /** 複数のHTTP段階を一つのテストにせず、適用POSTに必要な確認済み状態だけを用意する。 */
    private function prepareConfirmation(): array
    {
        $plan = app(SiteTemplateService::class)->plan(User::first(), $this->input);
        unset($plan['state']);
        $confirmation = ['token' => bin2hex(random_bytes(32)), 'expires_at' => time() + 900, 'plan' => $plan];
        $this->withSession(['site_template_confirmation' => $confirmation]);
        return $confirmation;
    }

    /** 管理画面の動的ロードを通さず、結果・公開画面・編集操作の前提となる生成データを用意する。 */
    private function applyTemplate(): SiteTemplateApplication
    {
        $actor = User::first();
        $service = app(SiteTemplateService::class);
        return $service->apply($actor, $service->plan($actor, $this->input));
    }

    /** 初期状態の拒否条件を確認するため、配置済みのフレームを用意する。 */
    private function createExistingFrame(): void
    {
        Frame::factory()->create(['page_id' => Page::first()->id, 'plugin_name' => 'contents', 'area_id' => 2]);
    }

    /** 表示不可の候補を確認するため、必要なプラグインを無効にする。 */
    private function disableBlogAndCalendar(): void
    {
        DB::table('plugins')->whereIn('plugin_name', ['Blogs', 'Calendars'])->update(['display_flag' => 0]);
    }

    /** 通常編集の経路を確認するため、実行者へ必要な編集権限を付ける。 */
    private function prepareEditingActor(): void
    {
        $actor = User::first();
        foreach (['role_article_admin', 'role_arrangement'] as $role) {
            UsersRoles::updateOrCreate(['users_id' => $actor->id, 'target' => 'base', 'role_name' => $role], ['role_value' => 1]);
        }
        UsersRoles::updateOrCreate(['users_id' => $actor->id, 'target' => 'manage', 'role_name' => 'admin_page'], ['role_value' => 1]);
        $this->actingAs($actor);
    }

    /** サブディレクトリの各HTTP段階を独立して確認するため、Webサーバー由来の情報を設定する。 */
    private function useSubdirectory(): void
    {
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost/cms');
        $this->withServerVariables(['SCRIPT_NAME' => '/cms/index.php', 'PHP_SELF' => '/cms/index.php', 'SCRIPT_FILENAME' => public_path('index.php')]);
    }
}
