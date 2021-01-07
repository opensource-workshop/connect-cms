<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Core\Configs;

class InsertIfAuthMethodIsNetcommons2AuthMethodEventConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // (旧)使用する外部認証 -> 使用する外部認証の設定。 外部認証毎に設定を保持するよう見直し
        $configs_auth_method = Configs::where('name', 'auth_method')->first();
        $auth_method = empty($configs_auth_method) ? null : $configs_auth_method->value;

        // もしnetcommons2の外部認証を使っていたら、(新)使用する外部認証に再設定する
        if ($auth_method == \AuthMethodType::netcommons2) {
            // (新)使用する外部認証
            $configs = new Configs();
            $configs->name = 'auth_method_event';
            $configs->category = 'auth';
            $configs->value = \AuthMethodType::netcommons2;
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
