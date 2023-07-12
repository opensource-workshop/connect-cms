<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Plugins extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'plugin_name', 'plugin_name_full', 'display_flag'
    ];

    /**
     * プラグインのクラス名とパスを取得
     */
    public static function getPluginClassNameAndFilePath(string $target_plugin): array
    {
        // クラスファイルの存在チェック。
        $file_path = base_path() . "/app/Plugins/User/" . ucfirst($target_plugin) . "/" . ucfirst($target_plugin) . "Plugin.php";

        // 各プラグインのgetWhatsnewArgs() 関数を呼び出し。
        $class_name = "App\Plugins\User\\" . ucfirst($target_plugin) . "\\" . ucfirst($target_plugin) . "Plugin";

        // ない場合はオプションプラグインを探す
        if (!file_exists($file_path)) {
            $file_path = base_path() . "/app/PluginsOption/User/" . ucfirst($target_plugin) . "/" . ucfirst($target_plugin) . "Plugin.php";
            $class_name = "App\PluginsOption\User\\" . ucfirst($target_plugin) . "\\" . ucfirst($target_plugin) . "Plugin";
        }

        return [$class_name, $file_path];
    }
}
