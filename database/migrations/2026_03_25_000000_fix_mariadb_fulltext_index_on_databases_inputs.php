<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixMariadbFulltextIndexOnDatabasesInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $result = DB::select("select version() as version");
        $version = $result[0]->version;

        // MariaDB以外はスキップ（MySQL環境では元のマイグレーションが正常動作しているため）
        if (strpos($version, 'Maria') === false) {
            return;
        }

        // FULLTEXTインデックスが既に存在する場合はスキップ
        $indexes = DB::select("
            SHOW INDEX FROM databases_inputs
            WHERE Key_name = 'ft_idx_databases_inputs_full_text'
        ");
        if (!empty($indexes)) {
            return;
        }

        // バージョン文字列から数字部分のみ抽出（"-log"等のサフィックスを除外するため preg_match を使用）
        if (!preg_match('/^(\d+)\.(\d+)\.(\d+)/', $version, $matches)) {
            // パース失敗は異常ケースのためログに記録
            Log::warning('FixMariadbFulltextIndexOnDatabasesInputs: MariaDBバージョン文字列のパース失敗。', [
                'version' => $version,
            ]);
            return;
        }

        $major = (int)$matches[1];
        $minor = (int)$matches[2];

        // MariaDBは5.6以上でFULLTEXT対応（NGRAMは使えないためパーサー指定なし）
        if ($major > 5 || ($major === 5 && $minor >= 6)) {
            DB::statement('ALTER TABLE databases_inputs ADD FULLTEXT INDEX ft_idx_databases_inputs_full_text (full_text);');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // インデックスが存在する場合のみ削除
        $indexes = DB::select("
            SHOW INDEX FROM databases_inputs
            WHERE Key_name = 'ft_idx_databases_inputs_full_text'
        ");
        if (!empty($indexes)) {
            DB::statement('ALTER TABLE databases_inputs DROP INDEX ft_idx_databases_inputs_full_text;');
        }
    }
}
