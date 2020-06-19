<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCovidDailyReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('covid_daily_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('covid_id');
            $table->date('target_date', 255)->nullable()->comment('日付');
            $table->string('fips', 255)->nullable()->comment('米：連邦情報処理標準コード');
            $table->string('admin2', 255)->nullable()->comment('郡名');
            $table->string('province_state', 255)->nullable()->comment('都道府県、州');
            $table->string('country_region', 255)->nullable()->comment('国・地域');
            $table->string('last_update', 255)->nullable()->comment('更新日');
            $table->string('lat', 255)->nullable()->comment('緯度');
            $table->string('long_', 255)->nullable()->comment('経度');
            $table->integer('confirmed')->nullable()->comment('感染者数');
            $table->integer('deaths')->nullable()->comment('死亡者数');
            $table->integer('recovered')->nullable()->comment('回復者数');
            $table->integer('active')->nullable()->comment('感染中');
            $table->string('combined_key', 255)->nullable()->comment('国＆群等');
            $table->string('incidence_rate', 255)->nullable()->comment('Admin2 + Province_State + Country_Region.');
            $table->string('case_fatality_ratio', 255)->nullable()->comment('10万人あたりの確定症例');
            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('deleted_id')->nullable();
            $table->string('deleted_name', 255)->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('covid_daily_reports');
    }
}
