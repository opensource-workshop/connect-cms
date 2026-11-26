<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetCategoryIdsToWhatsnews extends Migration
{
    public function up()
    {
        Schema::table('whatsnews', function (Blueprint $table) {
            $table->text('target_category_ids')->nullable();
        });
    }

    public function down()
    {
        Schema::table('whatsnews', function (Blueprint $table) {
            $table->dropColumn('target_category_ids');
        });
    }
}
