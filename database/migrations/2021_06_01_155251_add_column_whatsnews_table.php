<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Enums
use App\Enums\Bs4Color;
use App\Enums\RadiusType;

class AddColumnWhatsnewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsnews', function (Blueprint $table) {
            $table->integer('read_more_use_flag')->default(0)->comment('もっと見る使用フラグ')->after('important');
            $table->string('read_more_name', 191)->default('もっと見る')->comment('もっと見るボタン名')->after('read_more_use_flag');
            $table->integer('read_more_fetch_count')->default(5)->comment('もっと見る取得件数／回')->after('read_more_name');
            $table->string('read_more_btn_color_type', 191)->default(Bs4Color::primary)->comment('（ボタンデザイン）ボタンの色区分')->after('read_more_fetch_count');
            $table->string('read_more_btn_type', 191)->default(RadiusType::rounded)->comment('（ボタンデザイン）ボタンの形区分')->after('read_more_btn_color_type');
            $table->integer('read_more_btn_transparent_flag')->default(0)->comment('（ボタンデザイン）透過使用フラグ')->after('read_more_btn_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsnews', function (Blueprint $table) {
            $table->dropColumn('read_more_use_flag');
            $table->dropColumn('read_more_name');
            $table->dropColumn('read_more_fetch_count');
            $table->dropColumn('read_more_btn_color_type');
            $table->dropColumn('read_more_btn_type');
            $table->dropColumn('read_more_btn_transparent_flag');
        });
    }
}
