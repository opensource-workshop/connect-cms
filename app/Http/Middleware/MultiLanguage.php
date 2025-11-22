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

        //     多言語設定がonかの確認
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
        $slash_pos2 = strpos($page['permanent_link'], '/', 1);
        if ($slash_pos2 === false) {
            $language_root = $page['permanent_link'];
        } else {
            $language_root = substr($page['permanent_link'], 0, $slash_pos2);
        }
        // 言語ルートのデバッグ表示
        //echo $language_root . "<br />";
        //echo strpos($page['permanent_link'], '/', 1) . "<br />";

        // 言語ルートをキーにして多言語設定を絞り込み
        $configs = $request->attributes->get('configs');
        $configs_multilanguage = $configs->where('category', 'language')->where('additional1', $language_root);
        //print_r($configs_multilanguage);

        // 現在のページが多言語設定にない場合は次のmiddlewareへ
        if ($configs_multilanguage->isEmpty()) {
            return $next($request);
        }

        // パスの2つ目の / 以前を取り除く（言語ルート以外のURLを特定する）
        $two_slash_pos = strpos($page['permanent_link'], '/', 1);
        if ($two_slash_pos === false) {
            // $no_lang_path = substr($page['permanent_link'], 0, 1);
            $no_lang_path = "";
        } else {
            $no_lang_path = substr($page['permanent_link'], $two_slash_pos);
        }

        // configの多言語、pageテーブルをjoinし、同じ意味のパスの多言語のページ一覧を取得する。
        $multilanguage_pages = Page::join('configs', function ($join) use($no_lang_path) {
            $join->on('configs.category', '=', DB::raw("'language'"))
                 ->on('pages.permanent_link', 'LIKE', DB::raw("CONCAT(configs.additional1, '" . $no_lang_path . "')" ));
        })
        ->select('pages.*', 'configs.additional1')
        ->get();

        // 多言語のページ一覧を画面に渡すためにリクエストに設定する。
        $alternates = [];
        foreach($multilanguage_pages as $page) {
            $alternates[str_replace('/', '', $page->additional1)] = $page->permanent_link;
        }
        $request->attributes->add(['alternates' => $alternates]);
        //print_r($alternates);

        return $next($request);
    }
}
