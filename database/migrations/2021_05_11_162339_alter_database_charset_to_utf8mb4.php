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
     *     - 1. データベース文字セット変更（utf8 => utf8mb4）
     *     - 2. INDEX対象の一部カラムのデータ長さ変更（255 => 191）
     *     - 3. テーブル文字セット変更（utf8 => utf8mb4）
     *
     * @return void
     */
    public function up()
    {
        /**
         * データベースへのマイグレーション（utf8 => utf8mb4）
         */
        $database_name = env('DB_DATABASE');
        // データベースのデフォルト文字コード、照合順序の変更
        DB::connection()->getpdo()->exec('ALTER DATABASE ' . "`$database_name`" . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        /**
         * テーブルへのマイグレーションに先立って、カラムのデータ長が192より大きいカラムに対してINDEXを設定しているカラムの定義を変更する（varchar(191)にする）
         * ※データ長が191より大きいとutf8mb4化する際にINDEXサイズオーバーのエラーが発生する。
         * 「General error: 1709 Index column size too large. The maximum column size is 767 bytes.」
         *     - Laravelのmigration時にstring型をsize指定なしで実行した場合「varchar(255)」で作成される。
         *     - 文字長がutf8は3byteだがutf8mb8は4byteの為、テーブルの文字コード変更時にデフォルトキー最大長（767byte）制約に引っかかってしまうのが原因。
         *         - (utf8)255 * 3 = 765  => (utf8mb4)255 * 4 = 1020
         *     - データ長が191であればINDEXサイズオーバーは発生しない。(utf8mb4)191 * 4 = 764
         */
        $connection = DB::connection();
        $schema_manager = $connection->getDoctrineSchemaManager();

        // fix: mysql8対応. カラムの大文字小文字をDBテーブルと合わせる。

        // INDEXが設定されているテーブル名とカラム名を取得
        $indexs = DB::table('INFORMATION_SCHEMA.STATISTICS')
            ->select(
                'TABLE_NAME',
                'COLUMN_NAME'
            )
            // あらかじめ弾けるカラム名は弾く
            ->whereNotIn('COLUMN_NAME', ['id','_lft','_rgt','parent_id'])
            ->where('TABLE_SCHEMA', $database_name)
            ->get();

        // INDEX対象カラムの内、varchar(191)より大きいカラムのサイズをvarchar(191)に揃える
        foreach($indexs as $arr_seq => $arr_table_and_column_names){

            // カラム定義を取得（before）
            $before_column = $connection->getDoctrineColumn(
                $arr_table_and_column_names->TABLE_NAME,
                $arr_table_and_column_names->COLUMN_NAME
            );

            if ($before_column->toArray()['type'] instanceof Doctrine\DBAL\Types\StringType && $before_column->toArray()['length'] > 191) {

                // log用文字列生成
                $before_column_info = '';
                foreach($before_column->toArray() as $key => $value){
                    $before_column_info .= "$key:$value, ";
                }

                // ALTER文 構築
                $column_modify_statement = "ALTER TABLE $arr_table_and_column_names->TABLE_NAME MODIFY COLUMN $arr_table_and_column_names->COLUMN_NAME varchar(191)";

                // not null制約があれば引き継ぐ
                if($before_column->toArray()['notnull'] == 'true'){
                    $column_modify_statement .= " NOT NULL";
                }

                // コメントがあれば引き継ぐ
                if(!empty($before_column->toArray()['comment'])){
                    $comment = $before_column->toArray()['comment'];
                    $column_modify_statement .= " COMMENT '$comment'";
                }

                // ALTER文 実行
                DB::statement($column_modify_statement);

                // カラム定義を取得（after）
                $after_column = $connection->getDoctrineColumn(
                    $arr_table_and_column_names->TABLE_NAME,
                    $arr_table_and_column_names->COLUMN_NAME
                );

                // log用文字列生成
                $after_column_info = '';
                foreach($after_column->toArray() as $key => $value){
                    $after_column_info .= "$key:$value, ";
                }

                // logに実行前後の結果を出力
                Log::info("execute column modify statement:$column_modify_statement");
                Log::info("    (bef)$before_column_info");
                Log::info("    (aft)$after_column_info");
            }
        }

        /**
         * テーブルへのマイグレーション
         */
        $table_names = $schema_manager->listTableNames();
        // 「sessions」テーブルのみ除外する
        $table_names_omit_session = array_diff($table_names, array('sessions'));
        $table_names_omit_session = array_values($table_names_omit_session);

        // 既存テーブルの文字コード、照合順序の変更
        foreach($table_names_omit_session as $table_name){
            $convert_statement = "ALTER TABLE `$table_name` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            DB::connection()->getpdo()->exec($convert_statement);
            Log::info("execute table convert statement:$convert_statement");
        }
    }

    /**
     * Reverse the migrations.
     *     - 1. データベース文字セット変更（utf8mb4 => utf8）
     *     - 2. テーブル文字セット変更（utf8mb4 => utf8）
     *     - 3. INDEX対象の一部カラムのデータ長さ変更（191 => 255）
     *
     * @return void
     */
    public function down()
    {
        /**
         * データベースへのマイグレーション（utf8mb4 => utf8）
         */
        $database_name = env('DB_DATABASE');
        // データベースのデフォルト文字コード、照合順序の変更
        DB::connection()->getpdo()->exec('ALTER DATABASE ' . "`$database_name`" . ' CHARACTER SET utf8 COLLATE utf8_general_ci');

        /**
         * テーブルへのマイグレーション
         */
        $connection = DB::connection();
        $schema_manager = $connection->getDoctrineSchemaManager();
        $table_names = $schema_manager->listTableNames();
        // 「sessions」テーブルのみ除外する
        $table_names_omit_session = array_diff($table_names, array('sessions'));
        $table_names_omit_session = array_values($table_names_omit_session);

        // 既存テーブルの文字コード、照合順序の変更
        foreach($table_names_omit_session as $table_name){
            $convert_statement = "ALTER TABLE `$table_name` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
            DB::connection()->getpdo()->exec($convert_statement);
            Log::info("execute table convert statement:$convert_statement");
        }

        /**
         * テーブルへのマイグレーションに先立って、カラムのデータ長が192より大きいカラムに対してINDEXを設定しているカラムの定義を変更する（varchar(191)にする）
         * ※データ長が191より大きいとutf8mb4化する際にINDEXサイズオーバーのエラーが発生する。
         * 「General error: 1709 Index column size too large. The maximum column size is 767 bytes.」
         *     - Laravelのmigration時にstring型をsize指定なしで実行した場合「varchar(255)」で作成される。
         *     - 文字長がutf8は3byteだがutf8mb8は4byteの為、テーブルの文字コード変更時にデフォルトキー最大長（767byte）制約に引っかかってしまうのが原因。
         *         - (utf8)255 * 3 = 765  => (utf8mb4)255 * 4 = 1020
         *     - データ長が191であればINDEXサイズオーバーは発生しない。(utf8mb4)191 * 4 = 764
         */
        $connection = DB::connection();
        $schema_manager = $connection->getDoctrineSchemaManager();

        // INDEXが設定されているテーブル名とカラム名を取得
        $indexs = DB::table('INFORMATION_SCHEMA.STATISTICS')
            ->select(
                'TABLE_NAME',
                'COLUMN_NAME'
            )
            // あらかじめ弾けるカラム名は弾く
            ->whereNotIn('COLUMN_NAME', ['id','_lft','_rgt','parent_id'])
            ->where('TABLE_SCHEMA', $database_name)
            ->get();

        // INDEX対象カラムの内、varchar(191)より大きいカラムのサイズをvarchar(191)に揃える
        foreach($indexs as $arr_seq => $arr_table_and_column_names){

            // カラム定義を取得（before）
            $before_column = $connection->getDoctrineColumn(
                $arr_table_and_column_names->TABLE_NAME,
                $arr_table_and_column_names->COLUMN_NAME
            );

            if ($before_column->toArray()['type'] instanceof Doctrine\DBAL\Types\StringType && $before_column->toArray()['length'] == '191') {

                // log用文字列生成
                $before_column_info = '';
                foreach($before_column->toArray() as $key => $value){
                    $before_column_info .= "$key:$value, ";
                }

                // ALTER文 構築
                $column_modify_statement = "ALTER TABLE $arr_table_and_column_names->TABLE_NAME MODIFY COLUMN $arr_table_and_column_names->COLUMN_NAME varchar(255)";

                // not null制約があれば引き継ぐ
                if($before_column->toArray()['notnull'] == 'true'){
                    $column_modify_statement .= " NOT NULL";
                }

                // コメントがあれば引き継ぐ
                if(!empty($before_column->toArray()['comment'])){
                    $comment = $before_column->toArray()['comment'];
                    $column_modify_statement .= " COMMENT '$comment'";
                }

                // ALTER文 実行
                DB::statement($column_modify_statement);

                // カラム定義を取得（after）
                $after_column = $connection->getDoctrineColumn(
                    $arr_table_and_column_names->TABLE_NAME,
                    $arr_table_and_column_names->COLUMN_NAME
                );

                // log用文字列生成
                $after_column_info = '';
                foreach($after_column->toArray() as $key => $value){
                    $after_column_info .= "$key:$value, ";
                }

                // logに実行前後の結果を出力
                Log::info("execute column modify statement:$column_modify_statement");
                Log::info("    (bef)$before_column_info");
                Log::info("    (aft)$after_column_info");
            }
        }
    }
}
