<?php

namespace App\Plugins;

use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;

use App\Models\Common\Numbers;

/**
 * プラグイン基底クラス
 *
 * 全てのプラグインの基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザープラグイン
 * @package Contoroller
 */
class PluginBase
{

    /*
        コンストラクタ
     */
    function __construct() {

        // PHP エラー捕捉のためのエラーハンドラを設定する。
        set_error_handler(array($this, 'cc_error_handler'));
    }

    /*
        エラーハンドラ
     */
    protected function cc_error_handler ( $errno, $errstr, $errfile, $errline, $errcontext ) {

        // 例外を投げる。
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    // プラグインの対象者（User or Manager）：view ファイルの呼び出しでディレクトリ判定に使用するため。
//    var $plugin_target = null;

    // プラグイン名：view ファイルの呼び出しでディレクトリ判定に使用するため。
//    var $plugin_name = null;

    /**
     * コンストラクタ（プラグインの対象者とプラグイン名を受け取る）
     *
     */
//    public function __construct($plugin_target = null, $plugin_name = null)
//    {
//        $this->plugin_target = $plugin_target;
//        $this->plugin_name = $plugin_name;
//    }

    /**
     * 関数の有無
     *
     */
    public function function_exists($function_name)
    {
        return method_exists($this, $function_name);
    }

    /**
     * 連番取得
     *
     */
    public function getNo($plugin_name = null, $buckets_id = null, $prefix = null)
    {
        // 連番データは、払いだした最大の数値を保持している状態。

        // firstOrCreate で最初の連番に 0 を指定して、取得した値をインクリメント
        $numbers = Numbers::firstOrCreate([
                               'plugin_name'   => $plugin_name,
                               'buckets_id'    => $buckets_id,
                               'prefix'        => $prefix
                            ],
                            [
                               'serial_number' => 0,
                            ]);

        // インクリメント
        $numbers->increment('serial_number', 1);

        return $numbers->serial_number;
    }
    /**
     * invoke（プラグインのフレーム用メソッドをコア（cms_frame.blade.php）から呼ぶ）
     *
     */
/*
    public function invoke($request, $page_id, $frame_id)
    {
        return $request['frame_action'];
    }
*/

    /**
     * レンダリングエンジンのう回路  (※ 保留 2019-03-15 @include で見に行くパスが変更できなかった)
     *
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

