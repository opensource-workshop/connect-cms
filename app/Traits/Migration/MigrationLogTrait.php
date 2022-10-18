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
     * ログのヘッダー出力
     * use する側で定義する
     * @see \App\Traits\Migration\MigrationLogTrait
     */
    // private $log_header = "page_id,block_id,category,message";

    /**
     * ログのパス
     * ログ自体はプログラムが途中でコケても残るように、append する。
     * ここは、ログファイルの名前を時分秒を付けて保存したいため、シングルトンでファイル名を保持するためのもの。
     */
    private $log_path = [];

    /**
     * エラーログ出力
     */
    private function putError(int $destination, string $message, ?string $detail = null, $block = null): void
    {
        $this->putLog($destination, $message, $detail, $block, 'error');
    }

    /**
     * モニターログ出力
     */
    private function putMonitor(int $destination, string $message, ?string $detail = null, $block = null): void
    {
        $this->putLog($destination, $message, $detail, $block, 'monitor');
    }

    /**
     * リンクチェックのログ出力
     */
    private function putLinkCheck(int $destination, string $message, ?string $detail = null, $block = null): void
    {
        $this->putLog($destination, $message, $detail, $block, 'link_check');
    }

    /**
     * ログ出力
     * destination = 0 : 出力なし、1 : ログ、2 : 標準出力、3 : ログ＆標準出力
     */
    private function putLog(int $destination, string $message, ?string $detail = null, $block = null, string $filename = 'migration'): void
    {
        if (!isset($this->log_header)) {
            // default
            $this->log_header = "page_id,block_id,category,message";
        }

        // 最初のみ。
        // ログのファイル名の設定
        if (!array_key_exists($filename, $this->log_path)) {
            $this->log_path[$filename] = "migration/logs/" . $filename . "_" . date('His') . '.log';

            // ログにヘッダー出力
            if (config('migration.MIGRATION_JOB_LOG')) {
                Storage::append($this->log_path[$filename], $this->log_header);
            }

            // 標準出力にヘッダー出力
            if (config('migration.MIGRATION_JOB_MONITOR')) {
                echo $this->log_header . "\n";
            }
        }

        // メッセージ組み立て
        $log_str = "";
        if (empty($block)) {
            $log_str .= ",,";
        } else {
            if ($block && get_class($block) === 'App\Models\Migration\Nc2\Nc2Block') {
                // nc2
                $log_str .= $block->page_id . ",";
                $log_str .= $block->block_id . ",";
            } elseif ($block && get_class($block) === 'App\Models\Migration\Nc3\Nc3Frame') {
                // nc3
                // $log_str .= $block->box_id . ",";
                $log_str .= $block->id . ",,";
            } else {
                $log_str .= ",,";
            }
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
