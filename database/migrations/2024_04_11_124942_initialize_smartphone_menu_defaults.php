<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\SmartphoneMenuTemplateType;

class InitializeSmartphoneMenuDefaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configs', function (Blueprint $table) {
            // nameにsmartphone_menu_templateが存在しなければ、valueをopencurrenttreeに設定したレコードを追加
            $config = DB::table('configs')->where('name', 'smartphone_menu_template')->first();
            if (empty($config)) {
                DB::table('configs')->insert([
                    'name' => 'smartphone_menu_template',
                    'value' => SmartphoneMenuTemplateType::opencurrenttree,
                    'category' => 'general',
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configs', function (Blueprint $table) {
            // 既存レコードを削除する恐れがある為、down()は空のままにしておく
        });
    }
}
