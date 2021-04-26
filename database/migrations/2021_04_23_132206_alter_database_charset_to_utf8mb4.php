<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlterDatabaseCharsetToUtf8mb4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * データベースへのマイグレーション
         */
        $database_name = env('DB_DATABASE');
        // データベースのデフォルト文字コード、照合順序の変更
        DB::connection()->getpdo()->exec('ALTER DATABASE ' . "`$database_name`" . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        /**
         * テーブルへのマイグレーション
         */
        $table_names = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach($table_names as $table_name){
            /**
             * 「General error: 1709 Index column size too large. The maximum column size is 767 bytes.」エラー対応
             *     - Laravelのmigration時にstring型をsize指定なしで実行した場合「varchar(255)」で作成される。
             *     - 文字長がutf8は3byteだがutf8mb8は4byteの為、テーブルの文字コード変更時にデフォルトキー最大長（767byte）制約に引っかかってしまうのが原因。
             *         - (utf8)255 * 3 = 765  (utf8mb4)255 * 4 = 1020
             */
            DB::connection()->getpdo()->exec('ALTER TABLE ' . "`$table_name`" . ' ROW_FORMAT=DYNAMIC');
            // 既存テーブルの文字コード、照合順序の変更
            DB::connection()->getpdo()->exec('ALTER TABLE ' . "`$table_name`" . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /**
         * データベースへのマイグレーション
         */
        $database_name = env('DB_DATABASE');
        // データベースのデフォルト文字コード、照合順序の変更
        DB::connection()->getpdo()->exec('ALTER DATABASE ' . "`$database_name`" . ' CHARACTER SET utf8 COLLATE utf8_general_ci');
        /**
         * テーブルへのマイグレーション
         */
        $table_names = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach($table_names as $table_name){
            DB::connection()->getpdo()->exec('ALTER TABLE ' . "`$table_name`" . ' ROW_FORMAT=COMPACT');
            // 既存テーブルの文字コード、照合順序の変更
            DB::connection()->getpdo()->exec('ALTER TABLE ' . $table_name . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci');
        }
    }
}
