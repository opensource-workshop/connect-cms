<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photoalbum_contents', function (Blueprint $table) {
            $table->index(
                ['photoalbum_id', 'parent_id', 'is_folder'],
                'photoalbum_contents_photoalbum_parent_folder_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photoalbum_contents', function (Blueprint $table) {
            $table->dropIndex('photoalbum_contents_photoalbum_parent_folder_idx');
        });
    }
};

