<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photoalbum_contents', function (Blueprint $table) {
            $table->integer('display_sequence')->default(0)->after('is_cover');
        });

        $contents = DB::table('photoalbum_contents')
            ->select('id', 'parent_id', '_lft')
            ->orderBy('parent_id')
            ->orderBy('_lft')
            ->get();

        $current_parent_id = null;
        $sequence = 0;

        foreach ($contents as $content) {
            if ($current_parent_id !== $content->parent_id) {
                $current_parent_id = $content->parent_id;
                $sequence = 0;
            }

            $sequence++;

            DB::table('photoalbum_contents')
                ->where('id', $content->id)
                ->update(['display_sequence' => $sequence]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photoalbum_contents', function (Blueprint $table) {
            $table->dropColumn('display_sequence');
        });
    }
};
