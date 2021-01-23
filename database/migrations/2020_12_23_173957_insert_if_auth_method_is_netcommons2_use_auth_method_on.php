<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Core\Configs;

class InsertIfAuthMethodIsNetcommons2UseAuthMethodOn extends Migration
{
    /**
     * Run the migrations.
     * 外部認証を使用するフラグの追加に伴うパッチ
     *
     * @return void
     */
    public function up()
    {
        // 使用する外部認証
        $configs_auth_method = Configs::where('name', 'auth_method')->first();
        $auth_method = empty($configs_auth_method) ? null : $configs_auth_method->value;

        // もしnetcommons2の外部認証を使っていたら、外部認証を使用するフラグをONで登録する
        if ($auth_method == \AuthMethodType::netcommons2) {
            // 登録: 外部認証を使用するフラグ
            $configs = new Configs();
            $configs->name = 'use_auth_method';
            $configs->category = 'auth';
            $configs->value = 1;
            $configs->save();
        }
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
