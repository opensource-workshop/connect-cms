<?php

namespace App\Plugins\Manage\MigrationManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Artisan;
use DB;
use File;
use PharData;

use App\Models\Core\Configs;

use App\Plugins\Manage\ManagePluginBase;

/**
 * 他システム移行クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 他システム移行
 * @package Contoroller
 */
class MigrationManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]        = array('admin_system');
        $role_ckeck_table["nc2migration"] = array('admin_system');

        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request)
    {
        // Config データの取得
        $configs = Configs::get();

        // Config データの変換
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config->name] = $config->value;
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.migration.nc2upload', [
            "function"           => __FUNCTION__,
            "plugin_name"        => "migration",
            "configs"            => $configs_array,
        ]);
    }

    /**
     *  NC2 フルバックアップ アップロード処理
     */
    public function nc2migration($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 確認のチェック
        $validator = Validator::make($request->all(), [
            'confirm_migration'   => ['required'],
        ]);
        $validator->setAttributeNames([
            'confirm_migration'   => 'データ移行に対する注意点',
        ]);

        if ($validator->fails()) {
            return redirect('manage/migration/index')
                       ->withErrors($validator)
                       ->withInput();
        }

        // ファイルのアップロードチェック。
        if (!$request->hasFile('nc2fullbackup')) {
            $validator = Validator::make($request->all(), []);
            $validator->errors()->add('nc2fullbackup_upload_error', 'ファイルを指定してください。');
            return $this->index($request)->withErrors($validator);
        }

        // ファイルの基礎情報
        $client_original_name = $request->file('nc2fullbackup')->getClientOriginalName();
        $mimetype             = $request->file('nc2fullbackup')->getClientMimeType();
        $extension            = $request->file('nc2fullbackup')->getClientOriginalExtension();

        // 拡張子チェック
        if (substr($client_original_name, -7) != '.tar.gz') {
            $validator = Validator::make($request->all(), []);
            $validator->errors()->add('nc2fullbackup_upload_error', '.tar.gz ファイル以外はアップロードできません。');
            return $this->index($request)->withErrors($validator);
        }

        // NetCommons2 フルバックアップのディレクトリクリア
        Storage::deleteDirectory('migration/oneclick');

        // ファイルの保存
        $filename = 'nc2_fullbackup.tar.gz';
        $request->file('nc2fullbackup')->storeAs('migration/oneclick/', $filename);

        // NetCommons2 フルバックアップの tar.gz の展開
        $nc2_fullbackup_path = storage_path('app/migration/oneclick/nc2_fullbackup.tar.gz');
        try {
            $phar = new PharData($nc2_fullbackup_path);
            $phar->extractTo(storage_path('app/migration/oneclick'), null, true);
        } catch (Exception $e) {
            Log::debug("NC2 FullBackup 展開エラー");
        }

        // フルバックアップのSQL を復元
        // MySQL の max_allowed_packet に注意。インポートファイルが大きい場合は、この値を更新すること。
        $sql = file_get_contents(storage_path('app/migration/oneclick/backup_full.sql'));
        DB::connection('nc2')->unprepared($sql);

        // migration_config.sample.ini のコピーのための読み込み
        $migration_config = file_get_contents(app_path('Traits/Migration/sample/migration_config/migration_config.sample.ini'));

        // migration_config の URL の編集
        $migration_config = str_replace('http://kuina-el.localhost', url('/', null, true), $migration_config);

        // migration_config の NC2_EXPORT_UPLOADS_PATH の編集
        $migration_config = str_replace('/path_to_nc2', storage_path('app/migration/oneclick/htdocs'), $migration_config);

        // migration_config.sample.ini の出力
        File::put(storage_path('app/migration/oneclick/migration_config.oneclick.ini'), $migration_config);

        // エクスポートコマンドの実行
        Artisan::call('command:ExportNc2 all');

        // インポートコマンドの実行
        Artisan::call('command:ImportSite all redo');

        // アップロード画面に戻る
        return redirect("/manage/migration");
    }
}
