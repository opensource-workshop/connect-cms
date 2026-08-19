<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Common\Page;
use App\Utilities\Preview\PreviewDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PreviewController extends Controller
{
    /** プレビュー画面はプラグイン管理者だけが利用できる */
    public function __construct()
    {
        $this->middleware(['auth', 'can:role_arrangement']);
    }

    /** 選択した画面サイズで対象ページを表示するプレビュー枠を返す */
    public function show(Request $request, $page_id)
    {
        if (!is_scalar($page_id)) {
            abort(404);
        }

        $page_id = filter_var($page_id, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($page_id === false) {
            abort(404);
        }

        $page = Page::find($page_id);
        if (empty($page)) {
            abort(404);
        }

        if (!$this->isPageRoute($page)) {
            abort(404);
        }

        $page_tree = $page->getPageTreeByGoingBackParent(null);
        if (!$page->isVisibleAncestorsAndSelf($page_tree)) {
            abort(403);
        }

        $device = PreviewDevice::normalize($request->query('preview_device'));

        return view('core.preview_device', [
            'device' => $device,
            'devices' => PreviewDevice::devices(),
            'frame_url' => PreviewDevice::frameUrl($page->permanent_link),
            'page_url' => $page->permanent_link,
        ]);
    }

    /**
     * DBから取得した固定URLが公開ページとして解決されるか判定する。
     * 外部URLやクエリ文字列などを含まないパスであることを確認したうえで、
     * Laravelのルーターで通常ページ表示用のget_allルートに一致する場合だけ許可する。
     */
    private function isPageRoute(Page $page): bool
    {
        $permanent_link = $page->permanent_link;
        if (!is_string($permanent_link) || strpos($permanent_link, '/') !== 0 ||
            strpos($permanent_link, '//') === 0 || preg_match('/[\\x00-\\x1F\\x7F\\\\]/', $permanent_link)) {
            return false;
        }

        $parts = parse_url($permanent_link);
        if ($parts === false || isset($parts['scheme']) || isset($parts['host']) || isset($parts['port']) ||
            isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            return false;
        }

        $page_request = Request::create($permanent_link, 'GET');

        return Route::getRoutes()->match($page_request)->getName() === 'get_all';
    }
}
