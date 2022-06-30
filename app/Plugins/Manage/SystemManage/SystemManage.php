<?php

namespace App\Plugins\Manage\SystemManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

use App\Models\Core\Configs;

use App\Plugins\Manage\ManagePluginBase;

/**
 * システム管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category システム管理
 * @package Controller
 * @plugin_title システム管理
 * @plugin_desc デバッグモードや使用メモリ、ログなどシステムの基本に関する機能が集まった管理機能です。
 */
class SystemManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]           = array('admin_system');
        $role_ckeck_table["updateDebugmode"] = array('admin_system');
        $role_ckeck_table["log"]             = array('admin_system');
        $role_ckeck_table["updateLog"]       = array('admin_system');
        $role_ckeck_table["server"]          = array('admin_system');
        $role_ckeck_table["updateServer"]    = array('admin_system');
        $role_ckeck_table["mail"]            = ['admin_system'];
        $role_ckeck_table["updateMail"]      = ['admin_system'];
        $role_ckeck_table["mailTest"]        = ['admin_system'];
        $role_ckeck_table["sendMailTest"]    = ['admin_system'];
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     * @method_title デバックモード
     * @method_desc デバックモードが表示されます。
     * @method_detail デバックモードは通常はOFF、調査が必要な場合のみONにしてください。
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // セッションのデバックモードは、null(env参照)、0(セッション内 OFF)、1(セッション内 On)
        // 初期値は環境変数
        $now_debug_mode = Config('app.debug');

        // セッションのデバックモードの取得
        $debug_mode_session = session('app_debug');

        // セッションに設定されていない状態
        // 環境変数のデバックモードの取得(現在の動作モード)
        if ($debug_mode_session == null or $debug_mode_session == '') {
            // 初期値のまま
        } elseif ($debug_mode_session === '0' or $debug_mode_session === '1') {
            $now_debug_mode = $debug_mode_session;
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.system.debug', [
            "function"          => __FUNCTION__,
            "plugin_name"       => "system",
            "now_debug_mode"    => $now_debug_mode,
        ]);
    }

    /**
     *  更新
     */
    public function updateDebugmode($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // debugモードをonにする場合
        if ($request->has('debug_mode')) {
            if ($request->debug_mode == '1') {
                Session::put('app_debug', '1');
            } else {
                Session::put('app_debug', '0');
            }
            Session::save();
        }

        // システム管理画面に戻る
        return redirect("/manage/system");
    }

    /**
     *  ログ設定画面表示
     *
     * @return view
     * @method_title エラーログ設定
     * @method_desc エラーログファイルの出力方法を変更できます。
     * @method_detail エラーログの形式は必要に応じて変更してください。
     */
    public function log($request, $page_id = null)
    {
        // Config データの取得
        $categories_configs = Configs::where('category', 'log')->get();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.system.log', [
            "function"           => __FUNCTION__,
            "plugin_name"        => "system",
            "categories_configs" => $categories_configs,
        ]);
    }

    /**
     *  ログ設定更新
     */
    public function updateLog($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ログファイルの形式
        $configs = Configs::updateOrCreate(
            ['name'     => 'log_handler'],
            ['category' => 'log',
             'value'    => $request->log_handler]
        );

        // ログファイル名の指定の有無
        $configs = Configs::updateOrCreate(
            ['name'     => 'log_filename_choice'],
            ['category' => 'log',
             'value'    => $request->log_filename_choice]
        );

        // ログファイル名
        $configs = Configs::updateOrCreate(
            ['name'     => 'log_filename'],
            ['category' => 'log',
             'value'    => $request->log_filename]
        );

        // ログ設定画面に戻る
        return redirect("/manage/system/log")->with('flash_message', '更新しました。');
    }

    /**
     * サーバ設定画面表示
     *
     * @return view
     * @method_title サーバ設定
     * @method_desc 画像リサイズ時のPHPメモリ数を設定できます。
     * @method_detail 画像リサイズでエラーになるような場合は増やしてください。
     */
    public function server($request, $page_id = null)
    {
        // Config データの取得
        $configs = Configs::where('category', 'server')->get();

        return view('plugins.manage.system.server', [
            "function"           => __FUNCTION__,
            "plugin_name"        => "system",
            "configs"            => $configs,
        ]);
    }

    /**
     * サーバ設定更新
     */
    public function updateServer($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 画像リサイズ時のPHPメモリ数
        $configs = Configs::updateOrCreate(
            ['name'     => 'memory_limit_for_image_resize'],
            ['category' => 'server',
             'value'    => $request->memory_limit_for_image_resize]
        );

        return redirect("/manage/system/server")->with('flash_message', '更新しました。');
    }

    /**
     * メール設定画面表示
     *
     * @return view
     * @method_title メール設定
     * @method_desc SMTPサーバ等のメール送信設定ができます。
     * @method_detail メール送信機能を使う場合は設定してください。
     */
    public function mail($request, $id = null)
    {
        return view('plugins.manage.system.mail', [
            "function"           => __FUNCTION__,
            "plugin_name"        => "system",
        ]);
    }

    /**
     * メール設定更新
     */
    public function updateMail($request, $id = null)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // envファイルを書き換える
        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, str_replace('MAIL_FROM_ADDRESS=' . config('mail.from.address'), "MAIL_FROM_ADDRESS={$request->mail_from_address}", file_get_contents($path)));

            // ${APP_NAME} は一度取り除いてから置換
            file_put_contents($path, str_replace('MAIL_FROM_NAME="${APP_NAME}"', "MAIL_FROM_NAME=\"{$request->mail_from_name}\"", file_get_contents($path)));
            file_put_contents($path, str_replace('MAIL_FROM_NAME="' . config('mail.from.name') . '"', "MAIL_FROM_NAME=\"{$request->mail_from_name}\"", file_get_contents($path)));

            file_put_contents($path, str_replace('MAIL_HOST=' . config('mail.host'), "MAIL_HOST={$request->mail_host}", file_get_contents($path)));
            file_put_contents($path, str_replace('MAIL_PORT=' . config('mail.port'), "MAIL_PORT={$request->mail_port}", file_get_contents($path)));
            file_put_contents($path, str_replace('MAIL_USERNAME=' . config('mail.username'), "MAIL_USERNAME={$request->mail_username}", file_get_contents($path)));
            file_put_contents($path, str_replace('MAIL_PASSWORD=' . config('mail.password'), "MAIL_PASSWORD={$request->mail_password}", file_get_contents($path)));
            file_put_contents($path, str_replace('MAIL_ENCRYPTION=' . config('mail.encryption'), "MAIL_ENCRYPTION={$request->mail_encryption}", file_get_contents($path)));
        }

        // .envファイルを変更したらキャッシュクリア
        // php artisan config:clear
        Artisan::call('config:clear');

        return redirect("/manage/system/mail")->with('flash_message', '更新しました。');
    }

    /**
     * メール送信テスト画面
     *
     * @method_title メール送信テスト
     * @method_desc メールの動作確認ができます。
     * @method_detail メールの動作確認する場合、「送信」ボタンを押してください。
     */
    public function mailTest($request, $id = null)
    {
        return view('plugins.manage.system.mail_test', [
            "function"           => __FUNCTION__,
            "plugin_name"        => "system",
        ]);
    }

    /**
     * メール送信
     */
    public function sendMailTest($request, $id = null)
    {
        // [TODO] toメールの必須validate, メールアドレス形式チェック

        // メールオプション
        $mail_options = ['subject' => $request->subject, 'template' => 'mail.send'];

        // [TODO] メール設定不備だと例外が発生する。キャッチして、エラー表示できるか。
        // メール送信（Trait のメソッド）
        $this->sendMail($request->email, $mail_options, ['content' => $request->body], 'SystemManage');

        return redirect("/manage/system/mailTest")->with('flash_message', 'メール送信しました。');
    }
}
