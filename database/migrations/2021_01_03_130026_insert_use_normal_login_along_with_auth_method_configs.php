<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Core\Configs;

class InsertUseNormalLoginAlongWithAuthMethodConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 外部認証と併せて、通常ログインも使用. DBになければinsert, あればupdate(基本新しい設定のため、inserのみ動作の想定)
        $configs = Configs::updateOrCreate(
            ['name' => 'use_normal_login_along_with_auth_method'],
            [
                'category' => 'auth',
                'value'    => 1
            ]
        );
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
