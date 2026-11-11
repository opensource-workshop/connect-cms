<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteTemplateApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('site_template_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('application_key', 191)->unique();
            $table->string('template_id');
            $table->string('template_checksum', 64);
            $table->string('applied_via', 3);
            $table->integer('created_id');
            $table->string('created_name');
            $table->integer('updated_id')->nullable();
            $table->string('updated_name')->nullable();
            $table->text('result')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_template_applications');
    }
}
