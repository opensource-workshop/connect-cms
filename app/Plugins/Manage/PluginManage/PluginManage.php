<?php

namespace App\Plugins\Manage\PluginManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use File;
use DB;

use App\Models\Core\Plugins;

use App\Plugins\Manage\ManagePluginBase;

/**
 * プラグイン管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン管理
 * @package Contoroller
 */
class PluginManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]  = array('admin_system');
        $role_ckeck_table["update"] = array('admin_system');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // プラグインのini ファイル
        $plugin_inis = array();

        // Plugins データの取得
        $plugins = Plugins::orderBy('display_sequence', 'asc')->get();

        // プラグインのディレクトリの取得
        $directories = File::directories(app_path().'/Plugins/User');

        // オプション・プラグインのディレクトリの取得
        $option_directories = File::directories(app_path().'/PluginsOption/User');
        $directories = array_merge($directories, $option_directories);

        // プラグインのini ファイルの取得
        foreach ($directories as $dirkey => $directorie) {
            // ini ファイルがあれば、プラグインの日本語名を取得、プラグインの一覧に設定
            $is_plugin_record = false; // DB に登録されているかのフラグ
            if (File::exists($directorie."/plugin.ini")) {
                $plugin_inis = parse_ini_file($directorie."/plugin.ini");
                foreach ($plugins as $plugin) {
                    if (mb_strtolower($plugin->plugin_name) == mb_strtolower(basename($directorie))) {
                        //echo $plugin->plugin_name_full . "<br />";
                        $plugin->plugin_name_full = $plugin_inis['plugin_name_full'];
                        $plugin->is_directory = true;
                        $is_plugin_record = true;
                        continue;
                    }
                }
                // ini ファイルはあって、DB がないケース
                if (!$is_plugin_record) {
                    $new_plugin = new Plugins();
                    $new_plugin->plugin_name = basename($directorie);
                    $new_plugin->plugin_name_full = $plugin_inis['plugin_name_full'];
                    $new_plugin->is_directory = true;
                    $plugins[] = $new_plugin;
                }
            }
        }

        // 強制的に非表示にするプラグインを除外
        foreach ($plugins as $plugin_loop_key => $plugin) {
            if (in_array(mb_strtolower($plugin->plugin_name), config('connect.PLUGIN_FORCE_HIDDEN'))) {
                $plugins->forget($plugin_loop_key);
            }
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.plugin.plugin', [
            "function"    => __FUNCTION__,
            "plugin_name" => "plugin",
            "plugins"     => $plugins,
        ]);
    }

    /**
     *  更新
     */
    public function update($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        $max_display_sequence = 0;

        if ($request->plugins) {
            foreach ($request->plugins as $req_plugin) {
                $plugin = new Plugins();
                if ($req_plugin['id']) {
                    $plugin = Plugins::where('id', $req_plugin['id'])->first();
                }

                $plugin->plugin_name      = $req_plugin['plugin_name'];
                $plugin->plugin_name_full = $req_plugin['plugin_name_full'];

                $display_sequence = $req_plugin['display_sequence'];
                if (is_int(intval($display_sequence))) {
                    if ($max_display_sequence < intval($display_sequence)) {
                        $max_display_sequence = intval($display_sequence);
                    }
                    $display_sequence = intval($display_sequence);
                } else {
                    $max_display_sequence++;
                    $display_sequence = $max_display_sequence;
                }
                $plugin->display_sequence = $display_sequence;

                if (array_key_exists('display_flag', $req_plugin) && $req_plugin['display_flag'] == '1') {
                    $plugin->display_flag = $req_plugin['display_flag'];
                } else {
                    $plugin->display_flag = 0;
                }
                $plugin->save();
            }
        }

        // ページ管理画面に戻る
        return redirect("/manage/plugin");
    }
}
