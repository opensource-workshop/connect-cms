<?php

use App\Models\Core\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserRegisterFromConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ユーザ項目セット導入に伴い、複数の自動ユーザ登録設定を維持するための対応。additional1 = columns_set_id として扱う
        Configs::where('category', 'user_register')->where('additional1', null)->update(['additional1' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
