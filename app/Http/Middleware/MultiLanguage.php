<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Common\Page;
use App\Models\Core\Configs;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class MultiLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // alternate 対応。出力例は以下の通り。
        // 多言語設定の管理範囲のURLの場合、他の言語の情報を示す。

        // <link rel="alternate" href="xxx/ja" hreflang="ja" />
        // <link rel="alternate" href="xxx/en" hreflang="en" />
        // or
        // <link rel="alternate" href="xxx/ja-JP" hreflang="ja-JP" />
        // <link rel="alternate" href="xxx/en-GB" hreflang="en-GB" />

        // Configs
        $configs = $request->attributes->get('configs');

        // 多言語設定がonかの確認
        if (!empty($configs) &&
            $configs->isNotEmpty() &&
            $configs->where('name', 'language_multi_on')->where('value', '1')->isNotEmpty()) {
            // 多言語設定 on
            // 以下で多言語設定の処理を行う。
        } else {
            // 多言語設定 off
            // 次のmiddkewareへ。
            return $next($request);
        }

        /*
        SELECT *
        FROM pages as p
        INNER JOIN configs as c ON c.category = 'language' AND p.permanent_link = c.additional1

        SELECT *
        FROM pages as p
        INNER JOIN configs as c ON c.category = 'language' AND p.permanent_link LIKE CONCAT(c.additional1, '/test')
        */

        // alternate 対応。ここでは、URL パスの locale パス以降のパスを取得したい。
        // /es-ES      ⇒         (空白)
        // /es-ES/test ⇒ /test

        // ConnectPage で取得した page 情報から permanent_link を取得。
        // ブラウザのURLだと、プラグイン指定の場合などがあり、ページ管理のパスが取得できないケースがあるため、page 情報から。
        $page = $request->attributes->get('page');
        if (empty($page) || !isset($page->permanent_link)) {
            return $next($request);
        }

        /*
            カレントURLから言語ルートを特定する例
            /            ⇒ /
            /en-GB       ⇒ /en-GB
            /en-GB/test  ⇒ /en-GB

            config
            /            ⇒ 日本語ルートを多言語対象とする場合は、多言語設定に / を追加
            /en-GB       ⇒ 他言語のURL
            /es-ES       ⇒ 他言語のURL

            page         pageを検索する際の文字列
            /            ⇒ {config->locale}/      ⇒ Web ルートの場合、cofig を / で検索
            /en-GB       ⇒ {config->locale}/      ⇒ 言語ルートの場合、cofig を / で検索
            /en-GB/test  ⇒ {config->locale}/test
        */
        // カレントURLが多言語対象と判断するため、カレントURLの2つ目の / 以降を取り除いたものでconfig の多言語設定をチェック。
        // 上記のカレントURLから言語ルートを特定する例とpageの検索例のようにするために言語ルートを特定
        $language_root = $this->getLanguageRootPath($page['permanent_link']);

        // 言語ルートのデバッグ表示
        // \Log::debug($language_root);
        // \Log::debug(strpos($page['permanent_link'], '/', 1));

        // 言語ルートをキーにして多言語設定を絞り込み（現在のページが多言語設定の対象か判断するため）
        $configs = $request->attributes->get('configs');
        $configs_multilanguage = $configs->where('category', 'language')->where('additional1', $language_root);
        // \Log::debug(print_r($configs_multilanguage, true));

        // 現在のページが多言語設定にない場合は次のmiddlewareへ
        if ($configs_multilanguage->isEmpty()) {
            return $next($request);
        }

        // パスの2つ目の / 以前を取り除く（言語ルート以外のURLを特定する）
        //$two_slash_pos = strpos($page['permanent_link'], '/', 1);
        //if ($two_slash_pos === false) {
        //    // $no_lang_path = substr($page['permanent_link'], 0, 1);
        //    $no_lang_path = "";
        //} else {
        //    $no_lang_path = substr($page['permanent_link'], $two_slash_pos);
        //}

        // 言語ルート以外のパスを特定する。後で他言語の言語ルートをくっつけて、同じ意味で別言語のページを検索するため。
        $no_lang_path = str_replace($language_root, '', $page['permanent_link']);

        // 設定されている各言語のルートパスをconfigから絞り込む
        $configs_multilanguage_roots = $configs->where('category', 'language');
        // \Log::debug(print_r($configs_multilanguage_roots, true));

        // 各言語のルートパスと現在のパスを組み合わせて、whereInで現ページと同じ意味を持つ多言語ページのpermanent_linkを用意する。
        $multilanguage_ins = [];
        foreach ($configs_multilanguage_roots as $configs_multilanguage_root) {
            $multilanguage_in = $configs_multilanguage_root->additional1 . $no_lang_path;
            // '//'の変換は、configの多言語設定URLでトレイリングスラッシュのアリ/ナシをどちらも許容しているため。
            $multilanguage_ins[str_replace('/', '', $configs_multilanguage_root->additional1)] = str_replace('//', '/', $multilanguage_in);
        }
        // \Log::debug(print_r($multilanguage_ins, true));

        // Pageテーブルから現ページと同じ意味を持つ多言語ページのpermanent_linkを取得する。
        $multilanguage_pages = Page::select('pages.*')
            ->whereIn('pages.permanent_link', $multilanguage_ins)
            ->get();
        // \Log::debug($multilanguage_pages);

        // 多言語のページ一覧を画面に渡すためにリクエストに設定する。
        $alternates = [];
        foreach($multilanguage_pages as $page) {
            $alternates[$this->getLocale2Path($page->permanent_link)] = $page->permanent_link;
        }
        $request->attributes->add(['alternates' => $alternates]);
        // \Log::debug(print_r($alternates, true));

        return $next($request);
    }

    /**
     * パスの最初の部分を取得する。
     *
     * @param  $path 処理するパス
     * @param  $add_first 取得した最初のパスの前に付加する文字（データに /en-GB などと入っているため）
     * @return string パスの最初の部分（/がひとつもない場合は空を返す）
     */
    private function getLanguageRootPath($path, $add_first = '/')
    {
        $first_part = strtok($path, '/');
        if ($first_part === false) {
            return $add_first . "";
        }
        return $add_first . $first_part;
    }

    /**
     * パスからロケールを取得する。
     *
     * @param  $path 処理するパス
     * @return string ロケール文字列
     */
    private function getLocale2Path($path)
    {
        // パスからロケールの取得
        $locale = $this->getLanguageRootPath($path, '');

        // パスからロケールが取得できなかった場合（ルートの / でロケールを表す文字列がないなど）
        if (empty($locale)) {
            // 設定ファイルのデフォルトロケールを返す。
            $locale = App::getLocale();
        }

        return $locale;
    }
}
