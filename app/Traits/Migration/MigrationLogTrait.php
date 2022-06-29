<?php

namespace App\Traits\Migration;

use Illuminate\Support\Facades\Storage;

/**
 * 移行プログラム-ログ出力
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 移行
 * @package trait
 */
trait MigrationLogTrait
{
    /**
     * ログのパス
     * ログ自体はプログラムが途中でコケても残るように、append する。
     * ここは、ログファイルの名前を時分秒を付けて保存したいため、シングルトンでファイル名を保持するためのもの。
     */
    private $log_path = [];

    /**
     * エラーログ出力
     */
    private function putError($destination, $message, $detail = null, $nc2_block = null)
    {
        $this->putLog($destination, $message, $detail, $nc2_block, 'error');
    }

    /**
     * モニターログ出力
     */
    private function putMonitor($destination, $message, $detail = null, $nc2_block = null)
    {
        $this->putLog($destination, $message, $detail, $nc2_block, 'monitor');
    }

    /**
     * リンクチェックのログ出力
     */
    private function putLinkCheck($destination, $message, $detail = null, $nc2_block = null)
    {
        $this->putLog($destination, $message, $detail, $nc2_block, 'link_check');
    }

    /**
     * ログ出力
     * destination = 0 : 出力なし、1 : ログ、2 : 標準出力、3 : ログ＆標準出力
     */
    private function putLog($destination, $message, $detail, $nc2_block = null, $filename = 'migration')
    {
        // 最初のみ。
        // ログのファイル名の設定
        if (!array_key_exists($filename, $this->log_path)) {
            $this->log_path[$filename] = "migration/logs/" . $filename . "_" . date('His') . '.log';

            // ログにヘッダー出力
            if (config('migration.MIGRATION_JOB_LOG')) {
                Storage::append($this->log_path[$filename], "page_id,block_id,category,message");
            }

            // 標準出力にヘッダー出力
            if (config('migration.MIGRATION_JOB_MONITOR')) {
                echo "page_id,block_id,category,message" . "\n";
            }
        }

        // メッセージ組み立て
        $log_str = "";
        if (empty($nc2_block)) {
            $log_str .= ",,";
        } else {
            $log_str .= $nc2_block->page_id . ",";
            $log_str .= $nc2_block->block_id . ",";
        }
        $log_str .= $message . ",";
        $log_str .= $detail;


        // ログ出力
        if (config('migration.MIGRATION_JOB_LOG') && ($destination == 1 || $destination == 3)) {
            Storage::append($this->log_path[$filename], $log_str);
        }

        // 標準出力
        if (config('migration.MIGRATION_JOB_MONITOR') && ($destination == 2 || $destination == 3)) {
            echo $log_str . "\n";
        }
    }
}
