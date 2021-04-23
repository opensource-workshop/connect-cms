<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Common\Frame;

class AddContentOpenTypeFramesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->integer('content_open_type')->comment('コンテンツ公開区分')->after('display_sequence')->default(1);
            $table->timestamp('content_open_date_from')->comment('コンテンツ限定公開日時From')->after('content_open_type')->nullable();
            $table->timestamp('content_open_date_to')->comment('コンテンツ限定公開日時To')->after('content_open_date_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->dropColumn('content_open_type');
            $table->dropColumn('content_open_date_from');
            $table->dropColumn('content_open_date_to');
        });
    }
}
