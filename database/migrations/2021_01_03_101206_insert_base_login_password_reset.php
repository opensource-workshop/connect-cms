<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Core\Configs;

class InsertBaseLoginPasswordReset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // パスワードリセットの使用. DBになければinsert, あればupdate(基本base_login_password_resetは新しい設定のため、inserのみ動作の想定)
        $configs = Configs::updateOrCreate(
            ['name' => 'base_login_password_reset'],
            [
                'category' => 'general',
                'value' => 1
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
