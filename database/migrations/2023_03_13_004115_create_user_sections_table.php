<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('利用者ID');
            $table->unsignedBigInteger('section_id')->comment('組織ID');

            $table->integer('created_id')->nullable();
            $table->string('created_name')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name')->nullable();
            $table->timestamp('updated_at')->nullable();

            // 外部キー
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->restrictOnDelete();
            // 一意制約
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_sections');
    }
}
