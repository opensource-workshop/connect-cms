<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFullTextSearchToDatabases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases', function (Blueprint $table) {
            $table->integer('full_text_search')->default(0)->comment('全文検索を使用する')->after('numbering_prefix');
        });

        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->mediumText('full_text')->nullable()->comment('全文検索用項目')->after('categories_id');
        });

        // Laravel10対応(Laravel8でも動作)
        $result = DB::select("select version() as version");
        $version =  $result[0]->version;
        // MariaDBはNGRAMが使えない
        if (strpos($version, 'Maria') !== false) {
            // MariaDB
            $version = str_replace('-MariaDB', '', $version);
            $version_arr = explode('.', $version);
            // MariaDBは5.6以上でFULLTEXT対応
            if ($version_arr[0] >= 5 && $version_arr[1] >= 6) {
                DB::statement('ALTER TABLE databases_inputs ADD FULLTEXT INDEX ft_idx_databases_inputs_full_text (full_text);');
            }
        } else {
            DB::statement('ALTER TABLE databases_inputs ADD FULLTEXT INDEX ft_idx_databases_inputs_full_text (full_text) WITH PARSER ngram;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases', function (Blueprint $table) {
            $table->dropColumn('full_text_search');
        });

        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->dropIndex('ft_idx_databases_inputs_full_text');
            $table->dropColumn('full_text');
        });

    }
}
