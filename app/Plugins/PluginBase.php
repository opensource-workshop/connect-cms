<?php

namespace App\Plugins;

use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;

use Illuminate\Support\Facades\File;

use App\Models\Common\Numbers;
use App\Traits\ConnectMailTrait;

/**
 * プラグイン基底クラス
 *
 * 全てのプラグインの基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザープラグイン
 * @package Controller
 */
class PluginBase
{
    use ConnectMailTrait;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // PHP エラー捕捉のためのエラーハンドラを設定する。
        set_error_handler(array($this, 'ccErrorHandler'));
    }

    /**
     * エラーハンドラ
     */
    protected function ccErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        // 例外を投げる。
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    // プラグインの対象者（User or Manager）：view ファイルの呼び出しでディレクトリ判定に使用するため。
    // var $plugin_target = null;

    // プラグイン名：view ファイルの呼び出しでディレクトリ判定に使用するため。
    // var $plugin_name = null;

    /**
     * コンストラクタ（プラグインの対象者とプラグイン名を受け取る）
     *
     */
/*
    public function __construct($plugin_target = null, $plugin_name = null)
    {
        $this->plugin_target = $plugin_target;
        $this->plugin_name = $plugin_name;
    }
*/

    /**
     * 関数の有無
     */
/*
    public function function_exists($function_name)
    {
        return method_exists($this, $function_name);
    }
*/

    /**
     * 連番取得
     */
    public function getNo($plugin_name = null, $buckets_id = null, $prefix = null)
    {
        // 連番データは、払いだした最大の数値を保持している状態。

        // firstOrCreate で最初の連番に 0 を指定して、取得した値をインクリメント
        $numbers = Numbers::firstOrCreate(
            [
                'plugin_name'   => $plugin_name,
                'buckets_id'    => $buckets_id,
                'prefix'        => $prefix
            ],
            [
                'serial_number' => 0,
            ]
        );

        // インクリメント
        $numbers->increment('serial_number', 1);

        return $numbers->serial_number;
    }

    /**
     * テーマ名取得
     */
    private function getThemeName($dir, $theme_inis = null, $parent_dir = null)
    {
        // テーマ設定がない場合はディレクトリ名
        if (empty($theme_inis)) {
            return array('name' => basename($dir), 'dir' => ($parent_dir) ? $parent_dir . '/' . basename($dir) : basename($dir));
        }

        // テーマ設定からテーマ名
        if (array_key_exists('theme_name', $theme_inis)) {
            return array('name' => $theme_inis['theme_name'], 'dir' => ($parent_dir) ? $parent_dir . '/' . basename($dir) : basename($dir));
        } else {
            return array('name' => basename($dir), 'dir' => ($parent_dir) ? $parent_dir . '/' . basename($dir) : basename($dir));
        }
    }

    /**
     * テーマ取得
     */
    protected function getThemes()
    {
        // テーマディレクトリ
        $dirs = File::directories(public_path() . '/themes/');
        asort($dirs);  // ディレクトリが名前に対して逆順になることがあるのでソートしておく。
        //print_r($dirs);

        $themes = array();  // 画面に渡すテーマ配列
        foreach ($dirs as $dir) {
            if (File::exists($dir."/themes.ini")) {
                // テーマ設定ファイルのパース
                $theme_inis = parse_ini_file($dir."/themes.ini");

                // ディレクトリがテーマ・グループ用のものなら、その下のディレクトリを探す。
                if (array_key_exists('theme_dir', $theme_inis) && $theme_inis['theme_dir'] == 'group') {
                    $sub_themes = array();  // ディレクトリ管理のサブテーマ配列

                    // テーマの第2階層ディレクトリ
                    $group_dirs = File::directories(public_path() . '/themes/' . basename($dir));
                    asort($group_dirs);  // ディレクトリが名前に対して逆順になることがあるのでソートしておく。
                    foreach ($group_dirs as $group_dir) {
                        if (File::exists($group_dir."/themes.ini")) {
                            // テーマ設定ファイルのパース
                            $group_theme_inis = parse_ini_file($group_dir."/themes.ini");

                            // テーマ設定ファイルからテーマ名を探す。設定がなければディレクトリ名をテーマ名とする。
                            $sub_themes[] = $this->getThemeName($group_dir, $group_theme_inis, basename($dir));
                        } else {
                            $sub_themes[] = $this->getThemeName($group_dir, null, basename($dir));
                        }
                    }
                    // 第2階層テーマがある場合は選択肢に追加する。
                    if (!empty($sub_themes)) {
                        $themes[] = array('name' => $theme_inis['theme_name'], 'dir' => basename($dir), 'themes' => $sub_themes);
                    }
                } else {
                    // テーマ設定ファイルからテーマ名を探す。設定がなければディレクトリ名をテーマ名とする。
                    $themes[] = $this->getThemeName($dir, $theme_inis);
                }
            } else {
                $themes[] = $this->getThemeName($dir);
            }
        }
        return $themes;
    }

    /**
     * invoke（プラグインのフレーム用メソッドをコア（cms_frame.blade.php）から呼ぶ）
     */
/*
    public function invoke($request, $page_id, $frame_id)
    {
        return $request['frame_action'];
    }
*/

    /**
     * レンダリングエンジンのう回路  (※ 保留 2019-03-15 @include で見に行くパスが変更できなかった)
     */
/*
    public function view($dir, $arg = null)
    {
        $app = app();

        // 読み込み元のフォルダを指定
        $paths = [base_path('app/Plugins/' . $this->plugin_target . '/' . $this->plugin_name . '/views')];

        // もともとの設定を取得
        $originalFinder = View::getFinder();

        // 新しい設定を適用
        $finder = new FileViewFinder($app['files'], $paths);
        View::setFinder($finder);

        // レンダリング後のビューを文字列として取得
        $str = view($dir, $arg);

        // 設定をもとに戻す
        View::setFinder($originalFinder);

        // 画面内容を呼びもとに返す。
        return $str;
    }
*/
}
