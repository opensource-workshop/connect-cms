<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Core\ConnectController;
use App\Models\Common\Unsubscriber;
use App\Models\Core\Plugins;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * メール配信管理を呼び出す振り分けコントローラ
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メール配信管理
 * @package Controller
 */
class UnsubscribeController extends ConnectController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('connect.unsubscribe');
        // exceptで指定されたメソッドは除外する
        $this->middleware('connect.themes')->except('save');
    }

    /**
     * 処理の振り分け（GET）
     */
    public function invokeGetUnsubscribe(Request $request, $action = 'index')
    {
        // action チェック
        if (!in_array($action, ['index'])) {
            // 何もせずに戻る
            return;
        }

        // 該当の処理を呼ぶ
        return $this->$action($request, $action);
    }

    /**
     * 処理の振り分け（POST）
     * ※ メール購読解除リンク（List-Unsubscribe関連）からはPOSTでindex呼ばれる
     */
    public function invokePostUnsubscribe(Request $request, $action = 'index')
    {
        // action チェック
        if (!in_array($action, ['save', 'index'])) {
            // 何もせずに戻る
            return;
        }

        // 該当の処理を呼ぶ
        return $this->$action($request, $action);
    }

    /**
     * メール配信管理の表示
     */
    public function index($request, $action)
    {
        return $this->view('plugins.unsubscribe.index', [
            'themes' => $request->themes,
            'plugins' => $this->getUnsubscribePlugins(),
            'unsubscribers' => Unsubscriber::where('users_id', Auth::id())->get(),
        ]);
    }

    /**
     * メール配信管理の対象プラグインを取得
     *
     * １．DBのプラグインインストール一覧
     * ２．各プラグインのオプション含む、でメール配信管理を使うか？
     * ３．array作成してそれ返す
     */
    private function getUnsubscribePlugins(): Collection
    {
        $unsubscribe_plugins = collect();

        // プラグイン一覧の取得
        $plugins = Plugins::where('display_flag', 1)->orderBy('display_sequence')->get();

        foreach ($plugins as $i => $plugin) {
            // クラスファイルの存在チェック
            list($class_name, $file_path) = Plugins::getPluginClassNameAndFilePath($plugin->plugin_name);
            // ファイルの存在確認
            if (!file_exists($file_path)) {
                continue;
            }

            $class = new $class_name;

            // メール配信管理を使うか
            if ($class->use_unsubscribe) {
                $unsubscribe_plugins->put($i, $plugin);
            };
        }

        return $unsubscribe_plugins;
    }

    /**
     * メール配信管理の保存
     */
    public function save($request, $action)
    {
        $validator = Validator::make($request->all(), [
            'unsubscribed_flags' => ['required', 'array'],
        ]);
        $validator->setAttributeNames([
            'unsubscribed_flags' => 'メール配信',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        foreach ($request->unsubscribed_flags as $plugin_name => $unsubscribed_flag) {
            $unsubscriber = Unsubscriber::firstOrNew([
                'users_id' => Auth::id(),
                'plugin_name' => $plugin_name,
            ]);
            $unsubscriber->unsubscribed_flag = $unsubscribed_flag;
            $unsubscriber->save();
        }

        return redirect()->back()->with('flash_message', '更新しました。');
    }
}
