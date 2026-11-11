<?php

namespace App\Services\SiteTemplates;

use App\Enums\AreaType;
use App\Enums\StatusType;
use App\Enums\UserStatus;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\Core\FrameConfig;
use App\Models\Core\SiteTemplateApplication;
use App\Models\Core\UsersRoles;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Calendars\Calendar;
use App\Models\User\Calendars\CalendarFrame;
use App\Models\User\Contents\Contents;
use App\Models\User\Menus\Menu;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * サイトひな型の計画・適用処理。計画時にはサイトを更新しない。
 */
class SiteTemplateService
{
    private $definitions;
    private $preflight;

    public function __construct(SiteTemplateDefinition $definitions, SiteTemplatePreflight $preflight)
    {
        $this->definitions = $definitions;
        $this->preflight = $preflight;
    }

    public function templates(): array
    {
        return [$this->definitions->load('school-basic')];
    }

    public function authorize(User $actor): void
    {
        $current = User::find($actor->id);
        if (!$current || (int) $current->status !== UserStatus::active ||
            !UsersRoles::where('users_id', $actor->id)->where('target', 'manage')
                ->where('role_name', 'admin_system')->where('role_value', 1)->exists()) {
            throw new InvalidArgumentException('有効なシステム管理者を実行者に指定してください。');
        }
    }

    public function plan(User $actor, array $input): array
    {
        $this->authorize($actor);
        $validator = Validator::make($input, [
            'template_id' => 'required|string',
            'site_name' => ['required', 'string', 'max:100', 'regex:/^[^<>\x00-\x1f\x7f]+$/u'],
        ]);
        if ($validator->fails()) {
            throw new InvalidArgumentException('サイトひな型、サイト名（100文字以内のプレーンテキスト）を確認してください。');
        }
        $input = array_intersect_key($input, array_flip(['template_id', 'site_name']));
        $definition = $this->definitions->load($input['template_id']);
        $state = $this->preflight->inspect($definition);
        $this->preflight->assertReady($state);
        return [
            'actor_id' => $actor->id, 'input' => $input, 'definition' => $definition,
            'template_checksum' => $definition['checksum'], 'state_checksum' => $state['state_checksum'],
            'state' => $state, 'previous_site_name' => $state['site_name'], 'counts' => $this->definitions->counts($definition),
        ];
    }

    public function apply(User $actor, array $plan): SiteTemplateApplication
    {
        $this->authorize($actor);
        if ((int) $plan['actor_id'] !== (int) $actor->id) {
            throw new InvalidArgumentException('確認した実行者と一致しません。');
        }
        $this->preflight->assertStorageReady();
        try {
            $completed = DB::transaction(function () use ($actor, $plan) {
                $definition = $this->definitions->load($plan['input']['template_id']);
                $application = SiteTemplateApplication::create([
                    'application_key' => 'initial-site', 'template_id' => $definition['id'],
                    'template_checksum' => $definition['checksum'],
                    'applied_via' => 'web', 'created_id' => $actor->id, 'created_name' => $actor->name,
                ]);
                Page::where('permanent_link', '/')->lockForUpdate()->first();
                $current = $this->plan($actor, $plan['input']);
                if (!hash_equals($current['template_checksum'], $plan['template_checksum']) ||
                    !hash_equals($current['state_checksum'], $plan['state_checksum'])) {
                    throw new InvalidArgumentException('確認後にサイトまたはサイトひな型が変更されました。再確認してください。');
                }
                $application->result = $this->createSite($definition, $current['state'], $actor, $plan['input']['site_name']);
                $application->applied_at = now();
                $application->save();
                return $application;
            });
        } catch (QueryException $exception) {
            if (in_array((int) ($exception->errorInfo[1] ?? 0), [1062, 1205, 1213], true)) {
                throw new InvalidArgumentException('別の適用処理と競合しました。一覧で適用結果を確認してください。');
            }
            throw $exception;
        }
        Log::info('Site template applied', [
            'template_id' => $completed->template_id,
            'actor_id' => $actor->id, 'result' => 'success',
        ]);
        return $completed;
    }

    /**
     * 依存するページとバケツを先に保存し、論理キーを生成IDに変換する。
     */
    private function createSite(array $definition, array $state, User $actor, string $site_name): array
    {
        $pages = ['home' => $state['home']];
        $pages['home']->fill([
            'page_name' => $definition['pages'][0]['name'], 'layout' => '1|1|0|1', 'layout_inherit_flag' => 1,
        ])->save();
        $pending = array_filter($definition['pages'], function ($page) {
            return $page['key'] !== 'home';
        });
        while ($pending) {
            $ready = array_filter($pending, function ($item) use ($pages) {
                return $item['parent'] === null || isset($pages[$item['parent']]);
            });
            if (!$ready) {
                throw new InvalidArgumentException('ページ階層を作成できません。');
            }
            foreach ($ready as $index => $item) {
                $page = new Page([
                    'page_name' => $item['name'], 'permanent_link' => $item['url'], 'layout' => null,
                    'layout_inherit_flag' => 1, 'base_display_flag' => 1,
                ]);
                if ($item['parent'] === null) {
                    $page->saveAsRoot();
                } else {
                    $page->appendToNode($pages[$item['parent']])->save();
                }
                $pages[$item['key']] = $page;
                unset($pending[$index]);
            }
        }
        Page::recalcAllDepths();
        $buckets = [];
        $calendars = [];
        foreach ($definition['buckets'] as $item) {
            $bucket = Buckets::createWithDefaultPostRoles([
                'bucket_name' => $item['name'], 'plugin_name' => $item['plugin'],
            ], $state['configs']);
            $buckets[$item['key']] = $bucket;
            if ($item['plugin'] === 'blogs') {
                $this->saveAs(new Blogs(['bucket_id' => $bucket->id, 'blog_name' => $item['name']]), $actor);
            } elseif ($item['plugin'] === 'calendars') {
                $calendar = new Calendar(['bucket_id' => $bucket->id, 'name' => $item['name']]);
                $this->saveAs($calendar, $actor);
                $calendars[$item['key']] = $calendar;
            }
        }
        $frames = [];
        foreach ($definition['frames'] as $item) {
            $frame = Frame::create([
                'page_id' => $pages[$item['page']]->id, 'area_id' => $item['area'],
                'frame_title' => $item['bucket'] ? $buckets[$item['bucket']]->bucket_name : '',
                'frame_design' => 'default', 'plugin_name' => $item['plugin'], 'frame_col' => 12,
                'template' => $item['template'], 'bucket_id' => $item['bucket'] ? $buckets[$item['bucket']]->id : null,
                'page_only' => 0, 'display_sequence' => $item['sequence'],
            ]);
            $frames[$item['key']] = $frame;
            if ($item['plugin'] === 'calendars') {
                $this->saveAs(new CalendarFrame(['frame_id' => $frame->id, 'calendar_id' => $calendars[$item['bucket']]->id]), $actor);
            } elseif ($item['plugin'] === 'blogs') {
                $this->saveAs(new FrameConfig([
                    'frame_id' => $frame->id, 'name' => 'blog_view_count', 'value' => $item['page'] === 'home' ? '5' : '15',
                ]), $actor);
            }
        }
        foreach ($definition['menus'] as $item) {
            Menu::create([
                'frame_id' => $frames[$item['frame']]->id, 'select_flag' => 1,
                'page_ids' => implode(',', array_map(function ($key) use ($pages) {
                    return $pages[$key]->id;
                }, $item['pages'])),
            ]);
        }
        foreach ($definition['contents'] as $item) {
            $this->saveAs(new Contents([
                'bucket_id' => $buckets[$item['bucket']]->id, 'status' => StatusType::active,
                'content_text' => $this->definitions->renderHtml($item['html'], $site_name),
            ]), $actor);
        }
        Configs::updateOrCreate(['name' => 'base_site_name'], ['category' => 'general', 'value' => $site_name]);
        $ids = function ($models) {
            return array_map(function ($model) {
                return $model->id;
            }, $models);
        };
        $counts = $this->definitions->counts($definition);
        if (Page::count() !== count($pages) || Frame::count() !== $counts['フレーム'] || Buckets::count() !== $counts['バケツ'] ||
            Contents::count() !== $counts['固定記事'] || Menu::count() !== $counts['メニュー設定'] ||
            Blogs::count() !== $counts['ブログ設定'] || Calendar::count() !== $counts['カレンダー設定'] ||
            CalendarFrame::count() !== $counts['カレンダーフレーム設定'] || Page::isBroken()) {
            throw new \RuntimeException('生成結果の整合性を確認できませんでした。');
        }
        return ['counts' => $counts, 'pages' => $ids($pages), 'frames' => $ids($frames), 'buckets' => $ids($buckets)];
    }

    /**
     * 実行者を確実に記録するため、認証イベントに依存せず明示する。
     */
    private function saveAs($model, User $actor): void
    {
        $model->created_id = $actor->id;
        $model->created_name = $actor->name;
        $model->save();
    }
}
