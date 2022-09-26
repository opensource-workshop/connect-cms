<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Core\FrameConfig;

class AddNoFrameconfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frame_configs', function (Blueprint $table) {
            $table->integer('record_no')->default(0)->comment('複数設定用No')->after('name');

            // ユニークキー制約
            $table->unique(['frame_id', 'name', 'record_no']);
            $table->dropUnique(['frame_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frame_configs', function (Blueprint $table) {
            $table->dropColumn('record_no');

            // ユニークキー制約
            $table->unique(['frame_id', 'name']);
            $table->dropUnique(['frame_id', 'name', 'record_no']);
        });
    }
}
