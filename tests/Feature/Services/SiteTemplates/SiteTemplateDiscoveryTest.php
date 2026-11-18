<?php

namespace Tests\Feature\Services\SiteTemplates;

use App\Models\Common\Page;
use App\Services\SiteTemplates\SiteTemplateDefinition;
use App\Services\SiteTemplates\SiteTemplateService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Tests\TestCase;

/** JSONの配置だけで検出・生成できることを、独立した定義ディレクトリで確認する。 */
class SiteTemplateDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    private $directory;
    private $definitions;
    private $template;

    /** 他の確認環境のJSONを変更せず、追加テンプレートを用意する。 */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        \App\Models\Core\UsersRoles::updateOrCreate([
            'users_id' => User::first()->id, 'target' => 'manage', 'role_name' => 'admin_system',
        ], ['role_value' => 1]);
        $this->directory = sys_get_temp_dir() . '/site-templates-' . bin2hex(random_bytes(8));
        File::makeDirectory($this->directory);
        $this->definitions = new SiteTemplateDefinition($this->directory);
        $this->app->instance(SiteTemplateDefinition::class, $this->definitions);
        $this->template = json_decode(file_get_contents(resource_path('site_templates/school-basic/template.json')), true);
        $this->template['id'] = 'another-school';
        $this->template['pages'][0]['layout'] = '0|0|0|0';
        // 中央だけの構成にし、非表示エリアへフレームを置かない。
        $this->template['frames'] = array_values(array_filter($this->template['frames'], function ($frame) {
            return $frame['area'] === 2;
        }));
        $this->template['menus'] = [];
        foreach ($this->template['frames'] as &$frame) {
            if ($frame['plugin'] === 'blogs') {
                $frame['view_count'] = 8;
            }
        }
        unset($frame);
        $this->writeTemplate();
    }

    /** テストで作った一時定義のみを片付ける。 */
    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    /** 別IDのJSONを配置すれば一覧へ現れ、レイアウトと表示件数を反映して適用できる。 */
    public function testAddedJsonCanBeDiscoveredAndApplied(): void
    {
        $service = app(SiteTemplateService::class);
        $this->assertSame(['another-school'], array_column($service->templates(), 'id'));
        $actor = User::firstOrFail();
        $plan = $service->plan($actor, ['template_id' => 'another-school', 'site_name' => '追加学校']);
        $application = $service->apply($actor, $plan);
        $this->assertSame('another-school', $application->template_id);
        $this->assertSame('0|0|0|0', Page::where('permanent_link', '/')->value('layout'));
        $this->assertDatabaseHas('frame_configs', ['name' => 'blog_view_count', 'value' => '8']);
        $this->assertDatabaseCount('menus', 0);
    }

    /** 固定記事だけの構成でも、空のメニュー設定を許可して適用できる。 */
    public function testContentOnlyTemplateCanBeApplied(): void
    {
        $this->template['required_plugins'] = ['contents'];
        foreach (['frames', 'buckets'] as $type) {
            $this->template[$type] = array_values(array_filter($this->template[$type], function ($row) {
                return $row['plugin'] === 'contents';
            }));
        }
        $this->writeTemplate();
        $service = app(SiteTemplateService::class);
        $actor = User::firstOrFail();
        $service->apply($actor, $service->plan($actor, ['template_id' => 'another-school', 'site_name' => '固定記事学校']));
        $this->assertDatabaseCount('blogs', 0);
        $this->assertDatabaseCount('calendars', 0);
        $this->assertDatabaseCount('contents', 9);
    }

    /** 不正なJSONが混在しても正常な候補を検出でき、直接の読込みは拒否する。 */
    public function testMalformedJsonDoesNotHideValidTemplate(): void
    {
        File::makeDirectory($this->directory . '/broken');
        file_put_contents($this->directory . '/broken/template.json', '{invalid');
        $templates = $this->definitions->all();
        $this->assertSame(['another-school', 'broken'], array_column($templates, 'id'));
        $this->assertArrayHasKey('definition_error', $templates[1]);
        $this->expectException(InvalidArgumentException::class);
        $this->definitions->load('broken');
    }

    /** キー付きページ定義が混在しても、正常な候補は一覧に表示する。 */
    public function testKeyedPagesDoNotHideValidTemplate(): void
    {
        $broken = $this->template;
        $broken['id'] = 'broken';
        $broken['pages'] = array_column($broken['pages'], null, 'key');
        File::makeDirectory($this->directory . '/broken');
        file_put_contents($this->directory . '/broken/template.json', json_encode($broken));
        $templates = $this->definitions->all();
        $this->assertSame(['another-school', 'broken'], array_column($templates, 'id'));
        $this->assertArrayNotHasKey('definition_error', $templates[0]);
        $this->assertArrayHasKey('definition_error', $templates[1]);
    }

    /** 確認後のJSON変更ではサイトデータを作らず再確認を求める。 */
    public function testChangedJsonIsRejected(): void
    {
        $service = app(SiteTemplateService::class);
        $actor = User::firstOrFail();
        $plan = $service->plan($actor, ['template_id' => 'another-school', 'site_name' => '追加学校']);
        $this->template['name'] = '変更後';
        $this->writeTemplate();
        try {
            $service->apply($actor, $plan);
            $this->fail('変更した定義は再確認が必要です');
        } catch (InvalidArgumentException $exception) {
            $this->assertDatabaseCount('site_template_applications', 0);
            $this->assertDatabaseCount('pages', 1);
        }
    }

    /** ディレクトリ名とJSONのIDが異なる候補を拒否する。 */
    public function testMismatchedIdIsRejected(): void
    {
        $this->template['id'] = 'different';
        $this->writeTemplate();
        $this->expectException(InvalidArgumentException::class);
        $this->definitions->load('another-school');
    }

    /** テンプレートディレクトリ外を指すシンボリックリンクは読み込まない。 */
    public function testExternalSymlinkIsRejected(): void
    {
        symlink(resource_path('site_templates/school-basic'), $this->directory . '/school-basic');
        $this->expectException(InvalidArgumentException::class);
        $this->definitions->load('school-basic');
    }

    /** 確認後にJSONが削除された場合もサイトデータを書き込まない。 */
    public function testDeletedJsonIsRejected(): void
    {
        $service = app(SiteTemplateService::class);
        $actor = User::firstOrFail();
        $plan = $service->plan($actor, ['template_id' => 'another-school', 'site_name' => '追加学校']);
        unlink($this->directory . '/another-school/template.json');
        try {
            $service->apply($actor, $plan);
            $this->fail('削除された定義の適用は拒否する必要があります');
        } catch (InvalidArgumentException $exception) {
            $this->assertDatabaseCount('site_template_applications', 0);
            $this->assertDatabaseCount('pages', 1);
        }
    }

    /** 定義の変更を実ファイル経由で検証するため、一時JSONを保存する。 */
    private function writeTemplate(): void
    {
        $path = $this->directory . '/another-school';
        File::ensureDirectoryExists($path);
        file_put_contents($path . '/template.json', json_encode($this->template, JSON_UNESCAPED_UNICODE));
    }
}
