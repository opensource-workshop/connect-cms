<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetFrameIdsToFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('other_plugins_register_use_flag')->default(0)->comment('他プラグイン連携を使用する')->after('numbering_prefix');
            $table->text('target_frame_ids')->nullable()->comment('他プラグイン連携対象フレーム')->after('other_plugins_register_use_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('other_plugins_register_use_flag');
            $table->dropColumn('target_frame_ids');
        });
    }
}
