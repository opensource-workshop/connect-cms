<?php

namespace Tests\Unit\Services\SiteTemplates;

use App\Services\SiteTemplates\SiteTemplateDefinition;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * 同梱定義の件数・参照・上限を公開検証メソッドで確認する。
 */
class SiteTemplateDefinitionTest extends TestCase
{
    /** 学校ひな形は共有バケツを参照し、メニューにはバケツを作らない。 */
    public function testDefinition(): void
    {
        $service = new SiteTemplateDefinition();
        $definition = $service->load('school-basic');
        $this->assertSame(hash_file('sha256', resource_path('site_templates/school-basic/template.json')), $definition['checksum']);
        $this->assertSame([9, 1, 17, 12, 9, 2, 2, 1, 2], array_values($service->counts($definition)));
        foreach (['blogs', 'calendars'] as $plugin) {
            $frames = collect($definition['frames'])->where('plugin', $plugin);
            $this->assertSame((float) ($frames->count() / 2), (float) $frames->pluck('bucket')->unique()->count());
        }
        foreach (collect($definition['frames'])->where('plugin', 'menus') as $frame) {
            $this->assertNull($frame['bucket']);
        }
    }

    /** @dataProvider invalidDefinitions
     * 未許可設定、重複、参照不整合、循環、上限超過を保存前に拒否する。
     */
    public function testInvalidDefinition(string $case): void
    {
        $service = new SiteTemplateDefinition();
        $definition = $service->load('school-basic');
        unset($definition['checksum']);
        switch ($case) {
            case 'unknown':
                $definition['sql'] = 'invalid';
                break;
            case 'url':
                $definition['pages'][1]['url'] = '/';
                break;
            case 'cycle':
                $definition['pages'][1]['parent'] = $definition['pages'][1]['key'];
                break;
            case 'parent':
                $definition['pages'][1]['parent'] = 'missing';
                break;
            case 'bucket':
                $definition['frames'][0]['bucket'] = 'missing';
                break;
            case 'plugin':
                $definition['buckets'][0]['plugin'] = 'unknown';
                break;
            case 'setting':
                $definition['frames'][0]['template'] = '../../other';
                break;
            case 'key':
                $definition['pages'][1]['key'] = 'home';
                break;
            case 'pages':
                $definition['pages'] = array_fill(0, 51, $definition['pages'][0]);
                break;
            case 'frames':
                $definition['frames'] = array_fill(0, 201, $definition['frames'][0]);
                break;
            case 'buckets':
                $definition['buckets'] = array_fill(0, 101, $definition['buckets'][0]);
                break;
            case 'menu':
                $definition['menus'][0]['pages'][] = 'missing';
                break;
            case 'body':
                $definition['contents'][0]['html'] = ['invalid'];
                break;
            case 'missing-html':
                unset($definition['contents'][0]['html']);
                break;
            case 'legacy-body':
                $definition['contents'][0]['content_key'] = 'header';
                break;
            case 'html-limit':
                $definition['contents'][0]['html'] = str_repeat('x', 1048577);
                break;
        }
        $this->expectException(InvalidArgumentException::class);
        $service->validate($definition);
    }

    /** 不正な定義の代表例。 */
    public function invalidDefinitions(): array
    {
        return array_map(function ($case) {
            return [$case];
        }, [
            'unknown', 'url', 'cycle', 'parent', 'bucket', 'plugin', 'setting', 'key', 'pages', 'frames', 'buckets', 'menu', 'body', 'missing-html', 'legacy-body', 'html-limit',
        ]);
    }

    /** サイト名だけを安全に置換し、本文中のBlade・PHPの記法は実行しない。 */
    public function testHtmlInterpolation(): void
    {
        $html = '<h2>{{site_name}}</h2><p>{{site_name}}</p>{{ 1 + 1 }} @php echo "test"; @endphp';
        $actual = (new SiteTemplateDefinition())->renderHtml($html, '<学校> & "名称"');
        $this->assertSame('<h2>&lt;学校&gt; &amp; &quot;名称&quot;</h2><p>&lt;学校&gt; &amp; &quot;名称&quot;</p>{{ 1 + 1 }} @php echo "test"; @endphp', $actual);
    }

    /** 使うプラグインが異なるひな形では、必要な種類だけを宣言できる。 */
    public function testRequiredPluginsCanDifferBetweenTemplates(): void
    {
        $service = new SiteTemplateDefinition();
        $definition = $service->load('school-basic');
        unset($definition['checksum']);
        $definition['required_plugins'] = ['contents', 'menus'];
        foreach (['frames', 'buckets'] as $type) {
            $definition[$type] = array_values(array_filter($definition[$type], function ($row) {
                return in_array($row['plugin'], ['contents', 'menus'], true);
            }));
        }
        $service->validate($definition);
        $this->assertSame(['contents', 'menus'], $definition['required_plugins']);
        $definition['required_plugins'] = ['contents'];
        $this->expectException(InvalidArgumentException::class);
        $service->validate($definition);
    }

    /** @dataProvider unknownTemplates
     * 登録外のIDをファイルパスとして使用しない。
     */
    public function testUnknownTemplate(string $id): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new SiteTemplateDefinition())->load($id);
    }

    /** 登録外の識別子。 */
    public function unknownTemplates(): array
    {
        return [['../school-basic'], ['missing']];
    }
}
