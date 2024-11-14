<?php

namespace App\Models\Core;

use App\Utilities\Html\HtmlUtils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Configs extends Model
{
    use HasFactory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['name', 'value', 'category', 'additional1', 'additional2', 'additional3', 'additional4', 'additional5'];

    // move: App\Utilities\String\StringUtils::getNobrValue() に移動
    // /**
    //  * 値から改行を取りにぞいたものを返す
    //  */
    // public function getNobrValue()
    // {
    //     return str_replace("\r\n", "", $this->value);
    // }

    // delete: どこからも呼び出されてないためコメントアウト
    // /**
    //  * name をキーにした配列で返す。
    //  */
    // public static function getValues($name = null)
    // {
    //     if (empty($name)) {
    //         $configs = Configs::get();
    //     } else {
    //         $configs = Configs::where('name', $name)->get();
    //     }

    //     // Config データの変換
    //     foreach ($configs as $config) {
    //         $configs_array[$config->name] = $config->value;
    //     }
    //     return $configs_array;
    // }

    /**
     * 使用する外部認証 取得
     */
    public static function getAuthMethodEvent()
    {
        // 外部認証を使用
        $use_auth_method = Configs::where('name', 'use_auth_method')->first();

        // 外部認証を使用しない場合、newで戻す(空として扱う)
        if (empty($use_auth_method) || $use_auth_method->value == '0') {
            return new Configs();
        }

        // 使用する外部認証
        $auth_method_event = Configs::where('name', 'auth_method_event')->first();

        // 使用する外部認証がない場合、newで戻す(空として扱う)
        if (empty($auth_method_event) || $auth_method_event->value == '') {
            return new Configs();
        }

        // auth_method_eventで取得したconfigsを返す
        return $auth_method_event;
    }

    /**
     * 設定の値取得
     */
    public static function getConfigsValue($configs, $key, $default = false)
    {
        $config = $configs->firstWhere('name', $key);
        // firstWhere()で取得空の場合null が返ってくる。$config->value としても Null 合体演算子?? でundfind indexエラーでないので問題なし。nullなら default値 を返す。
        $value = $config->value ?? $default;

        return $value;
    }

    /**
     * 設定値（HTML）を修復して取得
     */
    public static function getConfigsValueWithHtmlRepair($configs, $key, $default = false)
    {
        $value = self::getConfigsValue($configs, $key, $default);
        // php8.x対応: nullだと HtmlPurifier::purify()内部で preg_replace(): Passing null to parameter #3 ($subject) of type array|string is deprecated エラー起こすため
        $value = is_null($value) ? '' : $value;

        // 閉じタグしかない等、壊れたHTMLを整形（＝修復）して出力するために HtmlPurifier を利用
        $purifier = HtmlUtils::getHtmlPurifier();
        $value = $purifier->purify($value);

        return $value;
    }

    /**
     * 設定の値取得. old対応あり
     */
    public static function getConfigsValueAndOld($configs, $key, $default = false)
    {
        $value = self::getConfigsValue($configs, $key, $default);

        // oldの値があれば、その値を使う
        $value = old($key, $value);
        return $value;
    }

    /**
     * 設定の任意クラス(値)を抽出（カンマ設定時はランダムで１つ設定）
     */
    public static function getConfigsRandValue($configs, $key, $default = '')
    {
        $value = self::getConfigsValue($configs, $key, $default);

        $values = explode(',', $value);
        $choise_value = $values[array_rand($values)];
        return $choise_value;
    }

    /**
     * 言語の取得
     * （ConnectController から移動してカスタマイズ）
     */
    public static function getLanguages()
    {
        $configs = self::getSharedConfigs();
        if (empty($configs)) {
            return null;
        }

        $languages = array();
        foreach ($configs as $config) {
            if ($config->category == 'language') {
                $languages[$config->additional1] = $config;
            }
        }
        return $languages;
    }

    /**
     * 全Configから設定の値取得
     */
    public static function getSharedConfigsValue($key, $default = false)
    {
        $configs = self::getSharedConfigs();
        return self::getConfigsValue($configs, $key, $default);
    }

    /**
     * 全Configの取得（Middlewareでセットされたもの）
     * （ConnectController から移動してカスタマイズ）
     *
     * @see \App\Http\Middleware\ConnectInit 全Congigsを request にセットしてる
     */
    public static function getSharedConfigs($format = null)
    {
        $request = app(Request::class);

        // Configs. app\Http\Middleware\ConnectInit.php でセットした全Configs
        $configs = $request->attributes->get('configs');
        // dd($request->attributes->get('configs'));

        if ($format == 'array') {
            return self::changeConfigsArray($configs);
        }
        return $configs;
    }

    /**
     * Configのarray変換
     * （ConnectController から移動してカスタマイズ）
     * @deprecated [TODO] 今後影響調査・対応して廃止したいなぁ
     */
    private static function changeConfigsArray($configs)
    {
        $return_array = array();

        foreach ($configs as $config) {
            $return_array[$config->name] = $config;
        }
        return $return_array;
    }

    /**
     * 値のチェック
     */
    public static function checkConfigValue($configs, $check_key, $check_value)
    {
        if ($configs->firstWhere('name', $check_key)->value == $check_value) {
            return true;
        }
        return false;
    }
}
