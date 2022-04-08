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
        'html_path', 'img_args', 'test_result', 'parent_id'
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
    public function getInsertionPdf($level, $position, $front = '', $rear = '', $manual_path = null)
    {
        // HTML用と同じタグを取得
        $insertion = $this->getInsertion($level, $position, $front, $rear);

        // タグをループして処理
        $match_ret = preg_match_all('/<([^>]*)>/', $insertion, $matches);
        if ($match_ret !== false && $match_ret > 0) {
            // タグを抜き出して、html_only クラスがあれば、そのタグを削除する。
            foreach ($matches[0] as $matche) {
                if (strpos($matche, 'html_only') !== false) {
                    $insertion = str_replace($matche, '', $insertion);
                }
            }

            // タグを抜き出して、img src があれば、画像のパスを実パスに変更する。
            foreach ($matches[1] as $matche) {
                if (strpos($matche, 'img src=') === 0) {
                    $tmp_path = str_replace('img src="', '', $matche);

                    // class 等の属性があれば、画像ファイルの後ろのスペース以降で切り離して捨てる（TCPDFで極端に小さな画像などになるので）。
                    if (strpos($tmp_path, ' ')) {
                        $img_option = mb_strstr($tmp_path, ' '); // 一応、属性以降を変数に入れているが、基本は使わない。（デバック用
                        $tmp_path = mb_strstr($tmp_path, ' ', true);
                    }

                    $tmp_path = str_replace('"', '', $tmp_path);
                    $img_path = "";
                    if (empty(config('connect.manual_put_base'))) {
                        if (\Storage::disk('manual')->exists('html/' . $this->category . '/' . $this->plugin_name . '/'. $this->method_name . '/'. $tmp_path)) {
                            $img_path = \Storage::disk('manual')->path('html/' . $this->category . '/' . $this->plugin_name . '/'. $this->method_name . '/'. $tmp_path);
                        }
                    } else {
                        if (\File::exists(config('connect.manual_put_base') . $this->category . '/' . $this->plugin_name . '/'. $this->method_name . '/'. $tmp_path)) {
                            $img_path = config('connect.manual_put_base') . $this->category . '/' . $this->plugin_name . '/'. $this->method_name . '/'. $tmp_path;
                        }
                    }
                    $insertion = str_replace($matche, 'img src="' . $img_path . '"', $insertion);
                }
            }
        }
        return $insertion;
    }

    /**
     * マニュアル用 mp4 データがあるか確認する。
     *
     * @return boolean
     */
    public function hasMp4($level = 1, $mp4dir = 'mp4')
    {
        if (\File::exists(config('connect.manual_put_base') . dirname($this->html_path, $level) . '/' . $mp4dir . '/mizuki/_video.mp4')) {
            return true;
        }
        return false;
    }

    /**
     * mp4 パスの返却
     *
     * @return boolean
     */
    public function getMp4Path($level = 1, $mp4dir = 'mp4')
    {
        return dirname($this->html_path, $level) . '/' . $mp4dir . '/mizuki/_video.mp4';
    }

    /**
     * ナレーション用に文章のクリーニング
     *
     * @return void
     */
    private static function cleaningText($text)
    {
        // HTML タグの除去
        $text = strip_tags($text);

        // （）の中身も含めた除去
        if (mb_strpos($text, '（') !== false && mb_strpos($text, '）') !== false) {
            $trim_start = mb_strpos($text, '（');
            $trim_end = mb_strpos($text, '）');
            $text = mb_substr($text, 0, $trim_start) . mb_substr($text, $trim_end + 1);
        }

        return $text;
    }

    /**
     * 実際のパスの取得
     */
    private static function getRealPath($disk, $path)
    {
        // リアルパスに変換
        $path = \Storage::disk($disk)->path($path);

        // Windows のffmpeg が / だとディレクトリを認識してくれない部分があるので、/ を \ に変換
        // エラーになっていたのは動画結合時の mp4_list のパス
        return str_replace('/', '\\', $path);
    }

    /**
     * 動画編集に必要なファイルパスや文章などを組み立てる。（メソッド用）
     */
    public function getMethodMaterials()
    {
        // img_args が json でない場合は動画を作成しない。
        if (!json_decode($this->img_args)) {
            return null;
        }

        /* 戻り値の例
        (
            [0] => Array
                (
                    [mp3_file_disk]   => user/blogs/index/mp3/mizuki/index.mp3
                    [mp4_dir_disk]    => user/blogs/index/mp4/mizuki
                    [mp4_list_disk]   => user/blogs/index/mp4/mizuki/_mp4list.txt
                    [mp4_fade_disk]   => user/blogs/index/mp4/mizuki/fade_index.mp4
                    [mp4_final_disk]  => user/blogs/index/mp4/mizuki/_video.mp4
                    [img_file_real]   => C:\***\tests\Browser\screenshots\user\blogs\index\images\index.png
                    [mp3_file_real]   => C:\***\tests\tmp\user\blogs\index\mp3\mizuki\index.mp3
                    [mp4_nofade_real] => C:\***\tests\tmp\user\blogs\index\mp4\mizuki\nofade_index.mp4
                    [mp4_fade_real]   => C:\***\tests\tmp\user\blogs\index\mp4\mizuki\fade_index.mp4
                    [mp4_fade_real2]  => C:\\SitesLaravel\\connect-cms\\htdocs\\test.localhost\\tests\\tmp\\user\\blogs\\index\\mp4\\mizuki\\fade_index.mp4
                    [mp4_list_real]   => C:\***\tests\tmp\user\blogs\index\mp4\mizuki\_mp4list.txt
                    [mp4_final_real]  => C:\***\tests\tmp\user\blogs\index\mp4\mizuki\_video.mp4
                    [mp4_manual_real] => C:\SitesLaravel\connect-cms-manual\user/blogs/index/mp4/mizuki/_video.mp4
                    [comment]         => 記事は新しいものから表示されます。
                )
            [1] => Array
        )
        */

        /* img_args の例
        [
            {"path": "user/blogs/index/images/index",
             "name": "記事の一覧",
             "comment": "<ul class=\"mb-0\"><li>記事は新しいものから表示されます。</li></ul>"
            },
            {"path": "user/blogs/index/images/index2",
             "name": "記事のコピー",
             "comment": "<ul class=\"mb-0\"><li>編集権限がある場合、記事の編集ボタンの右にある▼ボタンで、記事のコピーができます。</li></ul>"
            }
        ]
        */

        $materials = array();

        // 最初の動画の説明
        $first_comment  = 'ここでは、' . $this->plugin_title . '　プラグインの　' . $this->method_title . '　機能について説明します。';
        $first_comment .= empty($this->method_desc) ? '' : '機能概要、' . $this->method_desc;
        $first_comment .= empty($this->method_detail) ? '' : '機能詳細、' . $this->method_detail;
        $first_comment .= '　続いて、画面を説明します。';

        $json_paths = json_decode($this->img_args);
        foreach ($json_paths as $json_path) {
            // 画像にコメントがない場合は、動画を生成しない。
            if (property_exists($json_path, 'comment') && !empty($json_path->comment)) {
                // 処理を続ける。
            } else {
                continue;
            }

            // 動画の生成に必要な情報の組み立て（画像一つ分）
            $materials[] = self::getMaterial($json_path, $first_comment);

            $first_comment = '';
        }
        return $materials;
    }

    /**
     * 動画編集に必要なファイルパスや文章などを組み立てる。（画像一つ分）
     */
    private static function getMaterial($json_path, $first_comment)
    {
        // 基本に使う内容を変数に。
        $base_dir = dirname($json_path->path, 2);  // ベースのディレクトリ
        $basename = basename($json_path->path);    // 画像の基本名

        // 画面毎のナレーション文章
        $image_comment = $first_comment;
        if (property_exists($json_path, 'name')) {
            $image_comment .= $json_path->name . 'を説明します。';
        }

        // ナレーション文章や必要なファイルパスを組み立て
        $material = [
            'mp3_file_disk'   => $base_dir . '/mp3/mizuki/' . $basename . '.mp3',
            'mp4_list_disk'   => $base_dir . '/mp4/mizuki/' . '_mp4list.txt',
            'mp4_fade_disk'   => $base_dir . '/mp4/mizuki/' . 'fade_'. $basename . '.mp4',
            'mp4_nofade_disk' => $base_dir . '/mp4/mizuki/' . 'nofade_' . $basename . '.mp4',
            'mp4_final_disk'  => $base_dir . '/mp4/mizuki/' . '_video.mp4',
        ];
        $material = array_merge($material, [
            'img_file_real'   => self::getRealPath('screenshot', $json_path->path . '.png'),
            'mp3_file_real'   => self::getRealPath('tests_tmp', $material['mp3_file_disk']),
            'mp4_list_real'   => self::getRealPath('tests_tmp', $material['mp4_list_disk']),
            'mp4_fade_real'   => self::getRealPath('tests_tmp', $material['mp4_fade_disk']),
            'mp4_nofade_real' => self::getRealPath('tests_tmp', $material['mp4_nofade_disk']),
            'mp4_final_real'  => self::getRealPath('tests_tmp', $material['mp4_final_disk']),
        ]);
        $material = array_merge($material, [
            'mp4_fade_real2'  => str_replace('\\', '\\\\', $material['mp4_fade_real']), // ffmpeg concatは2重\が必要
            'mp4_manual_real' => config('connect.manual_put_base') . $material['mp4_final_disk'],
        ]);
        if (property_exists($json_path, 'comment') && !empty($json_path->comment)) {
            $material['comment'] = self::cleaningText($image_comment . $json_path->comment);
        } else {
            $material['comment'] = self::cleaningText($image_comment);
        }
        return $material;
    }

    /**
     * 動画編集に必要なファイルパスや文章などを組み立てる。（Plugin用）
     */
    public static function getPluginMaterials($methods)
    {
        $materials = array();

        // プラグイン内のメソッドのループ
        foreach ($methods as $method) {
            // 最初
            if (empty($materials)) {
                // 最初の動画の説明
                $first_comment  = 'ここでは、' . $method->plugin_title . '　プラグインの機能について説明します。';
                $first_comment .= empty($method->plugin_desc) ? '' : 'プラグインの詳細　' . $method->plugin_desc;
                $first_comment .= '　続いて、各機能を説明します。';
            }

            // 動画の生成に必要な情報の組み立て（画像一つ分）
            if (json_decode($method->img_args)) {
                // json版
                $json_paths = json_decode($method->img_args);
                $json_path = $json_paths[0];

            } elseif (!empty($method->img_args)) {
                // カンマ区切り画像パス版
                $img_args = explode(',', $method->img_args);
                $json_path = new \stdClass();
                $json_path->path = $img_args[0];
                $json_path->name = $method->method_title;
            }
            $json_path->comment  = $method->method_title . "を紹介します。　";
            $json_path->comment .= $method->method_title . 'では、' . $method->method_desc;

            // 動画の生成に必要な情報の組み立て（画像一つ分）
            $materials[] = self::getPluginMaterial($json_path, $first_comment);
            $first_comment = '';
        }
        return $materials;
    }

    /**
     * 動画編集に必要なファイルパスや文章などを組み立てる。（画像一つ分）
     */
    private static function getPluginMaterial($json_path, $first_comment)
    {
        // 基本に使う内容を変数に。
        $base_dir = dirname($json_path->path, 3);  // ベースのディレクトリ
        $basename = basename($json_path->path);    // 画像の基本名

        // ナレーション文章や必要なファイルパスを組み立て
        $material = [
            'mp3_file_disk'   => $base_dir . '/_mp3/mizuki/' . $basename . '.mp3',
            'mp4_list_disk'   => $base_dir . '/_mp4/mizuki/' . '_mp4list.txt',
            'mp4_fade_disk'   => $base_dir . '/_mp4/mizuki/' . 'fade_'. $basename . '.mp4',
            'mp4_nofade_disk' => $base_dir . '/_mp4/mizuki/' . 'nofade_' . $basename . '.mp4',
            'mp4_final_disk'  => $base_dir . '/_mp4/mizuki/' . '_video.mp4',
        ];
        $material = array_merge($material, [
            'img_file_real'   => self::getRealPath('screenshot', $json_path->path . '.png'),
            'mp3_file_real'   => self::getRealPath('tests_tmp', $material['mp3_file_disk']),
            'mp4_list_real'   => self::getRealPath('tests_tmp', $material['mp4_list_disk']),
            'mp4_fade_real'   => self::getRealPath('tests_tmp', $material['mp4_fade_disk']),
            'mp4_nofade_real' => self::getRealPath('tests_tmp', $material['mp4_nofade_disk']),
            'mp4_final_real'  => self::getRealPath('tests_tmp', $material['mp4_final_disk']),
        ]);
        $material = array_merge($material, [
            'mp4_fade_real2'  => str_replace('\\', '\\\\', $material['mp4_fade_real']), // ffmpeg concatは2重\が必要
            'mp4_manual_real' => config('connect.manual_put_base') . $material['mp4_final_disk'],
        ]);
        if (property_exists($json_path, 'comment') && !empty($json_path->comment)) {
            $material['comment'] = self::cleaningText($first_comment . $json_path->comment);
        } else {
            $material['comment'] = self::cleaningText($first_comment);
        }
        return $material;
    }

    /**
     * 動画編集に必要なファイルパスや文章などを組み立てる。（Category用）
     */
    public static function getCategoryMaterials($methods)
    {
        $materials = array();

        // カテゴリ内のプラグインのループ
        foreach ($methods as $plugin) {
            // 最初
            if (empty($materials)) {
                // 最初の動画の説明
                $first_comment  = 'ここでは、' . $plugin->category . '　カテゴリの各プラグインについて説明します。';
                $first_comment .= '　それでは、各プラグインを説明します。';
            }

            // 動画の生成に必要な情報の組み立て（画像一つ分）
            if (json_decode($plugin->img_args)) {
                // json版
                $json_paths = json_decode($plugin->img_args);
                $json_path = $json_paths[0];

            } elseif (!empty($plugin->img_args)) {
                // カンマ区切り画像パス版
                $img_args = explode(',', $plugin->img_args);
                $json_path = new \stdClass();
                $json_path->path = $img_args[0];
            } else {
                $json_path = new \stdClass();
            }
            $json_path->comment  = '　' . $plugin->plugin_title . "を紹介します。　";
            $json_path->comment .= $plugin->plugin_title . 'は、' . $plugin->plugin_desc;

            // 動画の生成に必要な情報の組み立て（画像一つ分）
            $materials[] = self::getCategoryMaterial($json_path, $first_comment, $plugin);
            $first_comment = '';
        }
        return $materials;
    }

    /**
     * 動画編集に必要なファイルパスや文章などを組み立てる。（各カテゴリの最初）
     */
    private static function getCategoryMaterial($json_path, $first_comment, $plugin)
    {
        // 基本に使う内容を変数に。
        $base_dir = dirname($json_path->path, 4);  // ベースのディレクトリ
        $basename = $plugin->plugin_name;          // プラグインの基本名

        // ナレーション文章や必要なファイルパスを組み立て
        $material = [
            'mp3_file_disk'   => $base_dir . '/_mp3/mizuki/' . $basename . '.mp3',
            'mp4_list_disk'   => $base_dir . '/_mp4/mizuki/' . '_mp4list.txt',
            'mp4_fade_disk'   => $base_dir . '/_mp4/mizuki/' . 'fade_'. $basename . '.mp4',
            'mp4_nofade_disk' => $base_dir . '/_mp4/mizuki/' . 'nofade_' . $basename . '.mp4',
            'mp4_final_disk'  => $base_dir . '/_mp4/mizuki/' . '_video.mp4',
        ];
        $material = array_merge($material, [
            'img_file_real'   => self::getRealPath('screenshot', $json_path->path . '.png'),
            'mp3_file_real'   => self::getRealPath('tests_tmp', $material['mp3_file_disk']),
            'mp4_list_real'   => self::getRealPath('tests_tmp', $material['mp4_list_disk']),
            'mp4_fade_real'   => self::getRealPath('tests_tmp', $material['mp4_fade_disk']),
            'mp4_nofade_real' => self::getRealPath('tests_tmp', $material['mp4_nofade_disk']),
            'mp4_final_real'  => self::getRealPath('tests_tmp', $material['mp4_final_disk']),
        ]);
        $material = array_merge($material, [
            'mp4_fade_real2'  => str_replace('\\', '\\\\', $material['mp4_fade_real']), // ffmpeg concatは2重\が必要
            'mp4_manual_real' => config('connect.manual_put_base') . $material['mp4_final_disk'],
        ]);
        if (property_exists($json_path, 'comment') && !empty($json_path->comment)) {
            $material['comment'] = self::cleaningText($first_comment . $json_path->comment);
        } else {
            $material['comment'] = self::cleaningText($first_comment);
        }
        return $material;
    }
}
