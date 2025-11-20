<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMailOauth2ToConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // メール認証方式の設定を追加（デフォルト: SMTP認証）
        DB::table('configs')->insert([
            'category' => 'mail',
            'name' => 'mail_auth_method',
            'value' => 'smtp',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // メール認証方式の設定を削除
        DB::table('configs')
            ->where('category', 'mail')
            ->where('name', 'mail_auth_method')
            ->delete();

        // OAuth2関連の設定も削除
        DB::table('configs')
            ->where('category', 'mail_oauth2_ms365_app')
            ->delete();
    }
}
