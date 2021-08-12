<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Common\Codes;

class UpdatePluginNameCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('codes', function (Blueprint $table) {
            //
        });

        // プラグイン名をすべて小文字に更新
        $codes = Codes::get();
        foreach ($codes as $code) {
            $code->update(['plugin_name' => strtolower($code->plugin_name)]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('codes', function (Blueprint $table) {
            //
        });

        // プラグイン名を先頭大文字に更新
        $codes = Codes::get();
        foreach ($codes as $code) {
            $code->update(['plugin_name' => ucfirst($code->plugin_name)]);
        }
    }
}
