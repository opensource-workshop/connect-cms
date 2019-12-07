<?php

namespace App\Models\Common;

use RecursiveIteratorIterator;
use RecursiveArrayIterator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use DB;

use Kalnoy\Nestedset\NodeTrait;

use App\Models\Core\Configs;

class Page extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['page_name', 'permanent_link', 'background_color', 'header_color', 'theme',  'layout', 'base_display_flag'];

    use NodeTrait;

    /**
     *  言語設定があれば、特定の言語ページのみに絞る
     *
     */
    public static function getPages($current_page_obj = null, $menu = null, $setting_mode = false)
    {
        // current_page_obj がない場合は、ページデータを全て取得（管理画面など）
        // 表示順は入れ子集合モデルの順番
        if (empty($current_page_obj)) {
            return self::defaultOrder()->get();
        }

        // メニューで表示するページが絞られている場合は、選択したページのみ取得する。
        $where_page_ids = array();
        if (!empty($menu) && $menu->select_flag == 1 && !empty($menu->page_ids)) {
            $where_page_ids = explode(',', $menu->page_ids);
        }

        // 多言語の使用有無取得
        $language_multi_on_record = Configs::where('name', 'language_multi_on')->first();
        $language_multi_on = ($language_multi_on_record) ? $language_multi_on_record->value : null;

        // 多言語モードでない場合 or 設定モードの場合 は表示設定されているページデータを全て取得
        if (!$language_multi_on || $setting_mode) {

            return self::defaultOrder()->where(function ($query_menu) use ($where_page_ids) {
                             // メニューによるページ選択
                             if (!empty($where_page_ids)) {
                                 $query_menu->whereIn('id', $where_page_ids);
                             }
                         })->get();
        }

        // 使用する言語リストの取得
        $languages = Configs::where('category', 'language')->orderBy('additional1', 'asc')->get();

        // 現在の言語
        $current_language = null;

        // 今、表示しているページの言語を判定
        $current_page_paths = explode('/', $current_page_obj['permanent_link']);
        if ($current_page_paths && is_array($current_page_paths) && array_key_exists(1, $current_page_paths)) {
            foreach($languages as $language) {
                if (trim($language->additional1, '/') == $current_page_paths[1]) {
                    $current_language = $current_page_paths[1];
                    break;
                }
            }
        }
        //echo $current_language;

        // 表示言語がデフォルトなら、多言語のページを表示しない。多言語なら、その言語のみに絞り込む。
        // デフォルトの場合、言語設定にある他の言語のpermanent_link を対象外にする。
        if (empty($current_language)) {
            $ret = self::defaultOrder()
                       ->where(function ($query) use ($languages) {
                           foreach($languages as $language) {
                               if ($language->additional1 == '/') {
                                   // デフォルト言語 "/" は表示するので、除外の対象外
                                   continue;
                               }
                               $query->where('permanent_link', 'not like', '%' . $language->additional1 . '%');
                           }
                       })
                       ->where(function ($query_menu) use ($where_page_ids) {
                           // メニューによるページ選択
                           if (!empty($where_page_ids)) {
                               $query_menu->whereIn('id', $where_page_ids);
                           }
                       })
                       ->get();

//Log::debug(json_encode( $ret, JSON_UNESCAPED_UNICODE));

            return $ret;
        }
        else {
            return self::defaultOrder()
                       ->where('permanent_link', 'like', '%/' . $current_language . '/%')
                       ->orWhere('permanent_link', '/' . $current_language)
                       ->where(function ($query_menu) use ($where_page_ids) {
                           // メニューによるページ選択
                           if (!empty($where_page_ids)) {
                               $query_menu->whereIn('id', $where_page_ids);
                           }
                       })
                       ->get();
        }
    }

    /**
     *  ページデータ取得＆深さの追加関数
     *
     * @param int $frame_id
     * @return view
     */
    public static function defaultOrderWithDepth($format = null, $current_page_obj = null, $menu = null, $setting_mode = false)
    {
        // ページデータを全て取得
        // 表示順は入れ子集合モデルの順番
        $pages = self::getPages($current_page_obj, $menu, $setting_mode);
        //Log::debug($pages);

        // メニューの階層を表現するために、一度ツリーにしたものを取得し、クロージャで深さを追加
        $tree = $pages->toTree();
        //Log::debug(json_encode( $tree, JSON_UNESCAPED_UNICODE));

        // クロージャでページ配列を再帰ループし、深さを追加する。
        // テンプレートでは深さをもとにデザイン処理する。
        $traverse = function ($pages, $prefix = '-', $depth = -1, $display_flag = 1) use (&$traverse) {
            $depth = $depth+1;
            foreach ($pages as $page) {
                $page->depth = $depth;
                //$page->page_name = $page->page_name;
                // 表示フラグを親を引き継いで保持
                $page->display_flag = ($page->base_display_flag == 0 || $display_flag == 0 ? 0 : 1);
                $traverse($page->children, $prefix.'-', $depth, $page->display_flag);
            }
        };
        $traverse($tree);

        if ( $format == 'flat' ) {
            return $pages;
        }

        return $tree;
    }

    /**
     *  リンク用URL取得
     *
     */
    public function getLinkUrl()
    {
        return $this->permanent_link;
    }

    /**
     *  CSS セレクタ用クラス用取得
     *
     */
    public function getPermanentlinkClassname()
    {
        return str_replace('/', '-', trim($this->permanent_link, '/'));
    }
}
