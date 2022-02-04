<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use Kalnoy\Nestedset\NodeTrait;

class Dusks extends Model
{
    // 入れ子集合モデル
    use NodeTrait;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'category', 'sort',
        'plugin_name', 'plugin_title', 'plugin_desc',
        'method_name', 'method_title', 'method_desc', 'method_detail',
        'html_path', 'img_args', 'test_result',
    ];

    /**
     * マニュアル用のデータの受け取り
     */
    public function setMethodManual($manual_docs)
    {
        $method_doc = $manual_docs[$this->method_name];
        $this->method_title  = $method_doc['title'];
        $this->method_desc   = $method_doc['desc'];
        $this->method_detail = $method_doc['detail'];
    }

    /**
     * 画像パスの配列
     */
    public function getImgArgs()
    {
        // 画像関係の設定を展開する。
        $ret_collection = new Collection();
        $img_json = json_decode($this->img_args);

        // 画像関係の設定がjson 文字列かで処理を分けて、共通の形式に保存する。
        if ($img_json) {
            foreach ($img_json as $img_arg) {
                $ret_collection->push([
                    "path" => $img_arg->path,
                    "name" => property_exists($img_arg, "name") ? $img_arg->name : "",
                    "comment" => property_exists($img_arg, "comment") ? $img_arg->comment : "",
                    "style" => property_exists($img_arg, "style") ? $img_arg->style : ""
                ]);
            }
        } else {
            foreach (explode(',', $this->img_args) as $img_path) {
                $ret_collection->push([
                    "path" => $img_path,
                    "name" => "",
                    "comment" => "",
                    "style" => ""
                ]);
            }
        }
        return $ret_collection;
    }

    /**
     * html_path を取得
     *
     * @return string
     */
    public function getHtmlPathAttribute()
    {
        if (empty($this->id)) {
            return "index.html";
        }
        return $this->attributes['html_path'];
    }

    /**
     * データ保存＆階層移動
     *
     * @return dusks
     */
    public static function putManualData($key, $value)
    {
        $dusk = Dusks::updateOrCreate($key, $value);

        // 結果の親子関係の紐づけ
        if ($dusk->method_name != 'index') {
            // 親を取得して、子のparent をセットして保存する。（_lft, _rgt は自動的に変更される）
            $parent = Dusks::where('category', $dusk->category)->where('plugin_name', $dusk->plugin_name)->where('method_name', 'index')->first();
            $dusk->parent_id = $parent->id;
            $dusk->save();
        }
    }

    /**
     * マニュアル用差込データの取得
     *
     * @return dusks
     */
    public function getInsertion($level, $position, $front = '', $rear = '')
    {
        $search_dir = '';
        if ($level == 'plugin') {
            $search_dir = 'insertion/' . $this->category . '/' . $this->plugin_name;
        } elseif ($level == 'method') {
            $search_dir = 'insertion/' . $this->category . '/' . $this->plugin_name . '/'. $this->method_name;
        }

        // ファイルの検索
        if (\Storage::disk('manual')->exists($search_dir . '/' . $position . '.txt')) {
            return $front . \Storage::disk('manual')->get($search_dir . '/' . $position . '.txt') . $rear;
        }
    }

    /**
     * マニュアル用差込データの取得 PDF用
     * タグにhtml_only 属性がついている場合、タグを削除する。
     *
     * @return dusks
     */
    public function getInsertionPdf($level, $position, $front = '', $rear = '')
    {
        // HTML用と同じタグを取得
        $insertion = $this->getInsertion($level, $position, $front, $rear);

        // タグを抜き出して、html_only クラスがあれば、そのタグを削除する。
        $match_ret = preg_match_all('/<([^>]*)>/', $insertion, $matches);
        if ($match_ret !== false && $match_ret > 0) {
            foreach ($matches[0] as $matche) {
                if (strpos($matche, 'html_only') !== false) {
                    $insertion = str_replace($matche, '', $insertion);
                }
            }
        }
        return $insertion;
    }
}
