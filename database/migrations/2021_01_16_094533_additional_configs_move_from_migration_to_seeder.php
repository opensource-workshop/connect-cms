<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Core\Configs;

class AdditionalConfigsMoveFromMigrationToSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // butfix: 追加configsは、マイグレーションではなくseederで設定する。そうしないと、新規インストール時に初期configがインストールされなくなる

        // パスワードリセットの使用
        Configs::where('name', 'base_login_password_reset')->delete();

        // マイページの使用
        Configs::where('name', 'use_mypage')->delete();

        // 外部認証と併せて、通常ログインも使用
        Configs::where('name', 'use_normal_login_along_with_auth_method')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
