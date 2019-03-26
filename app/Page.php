<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Page extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['page_name', 'permanent_link', 'path'];

    use NodeTrait;

    /**
     *  ページデータ取得＆深さの追加関数
     *
     * @param int $frame_id
     * @return view
     */
    public static function defaultOrderWithDepth()
    {
        // ページデータを全て取得
        // 表示順は入れ子集合モデルの順番
        $pages = self::defaultOrder()->get();

        // メニューの階層を表現するために、一度ツリーにしたものを取得し、クロージャで深さを追加
        $tree = $pages->toTree();

        // クロージャでページ配列を再帰ループし、深さを追加する。
        // テンプレートでは深さをもとにデザイン処理する。
        $traverse = function ($pages, $prefix = '-', $depth = -1) use (&$traverse) {
            $depth = $depth+1;
            foreach ($pages as $page) {
                $page->depth = $depth;
                $page->page_name = $page->page_name;
                $traverse($page->children, $prefix.'-', $depth);
            }
        };
        $traverse($tree);

        return $pages;
    }
}
