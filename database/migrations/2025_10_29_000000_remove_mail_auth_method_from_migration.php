<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * マイグレーションでConfigsレコードを作成するのを廃止
 *
 * 背景:
 * - 2025_10_09_160056_add_mail_oauth2_to_configs.php でmail_auth_methodをinsertしていた
 * - 新規インストール時、migrate実行後にConfigsテーブルにレコードが1件存在する状態になる
 * - DefaultConfigsTableSeeder の条件 if (Configs::count() == 0) が false になる
 * - 基本設定レコード（base_site_name等）が投入されず、エラーが発生する
 *
 * 対応:
 * - mail_auth_methodレコードを削除
 * - 以降はデフォルト値（MailAuthMethod::smtp）で動作
 * - 管理画面で認証方式変更時に自動作成される（updateOrCreate使用）
 *
 * 関連Issue:
 * - https://github.com/opensource-workshop/connect-cms/issues/2293
 */
class RemoveMailAuthMethodFromMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // マイグレーションで作成したmail_auth_methodレコードを削除
        DB::table('configs')
            ->where('category', 'mail')
            ->where('name', 'mail_auth_method')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // rollback時は元に戻す
        DB::table('configs')->insert([
            'category' => 'mail',
            'name' => 'mail_auth_method',
            'value' => 'smtp',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
