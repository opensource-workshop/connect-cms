<?php

namespace App\Utilities\Storage;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utilities\File\FileUtils;

/**
 * ストレージ使用量計算ユーティリティ
 */
class StorageUsageCalculator
{
    /**
     * 各種の使用量を配列に詰めて返す
     *
     * @return array
     */
    public static function getDataUsage(): array
    {
        $usage = [];

        // テーブル使用量
        $usage['tables'] = self::getTableUsageFormatted();

        // データファイル使用量（storage/app/uploads配下）
        $usage['uploads'] = FileUtils::getTotalUsageFormatted(storage_path('app/uploads'));

        // 総使用量（テーブル使用量 + アップロードファイル使用量）
        $usage['total'] = self::calculateTotalUsage($usage['tables'], $usage['uploads']);

        // プラン容量（env設定がある場合のみ）
        $usage['plan_limit'] = self::getPlanLimitFormatted();

        // 使用率（プラン容量が設定されている場合のみ）
        $usage['usage_percentage'] = self::getUsagePercentage($usage['total'], $usage['plan_limit']);

        return $usage;
    }

    /**
     * information_schema.tablesからテーブル使用量を算出してフォーマット済みの使用量を返す
     *
     * @return string
     */
    private static function getTableUsageFormatted(): string
    {
        try {
            $database_name = env('DB_DATABASE');
            
            // システムスキーマからテーブルの使用量を取得
            $result = DB::select("
                SELECT COALESCE(SUM(data_length + index_length), 0) as total_size 
                FROM information_schema.tables 
                WHERE table_schema = ? AND table_name NOT IN ('app_logs', 'migrations', 'migration_mappings', 'dusks', 'api_secrets')
            ", [$database_name]);
            
            $total_bytes = $result[0]->total_size ?? 0;
            
            return FileUtils::getFormatSizeDecimalPoint((int)$total_bytes);
        } catch (\Exception $e) {
            Log::error('StorageUsageCalculator: Table usage calculation failed', ['error' => $e->getMessage()]);
            return '0B';
        }
    }

    /**
     * 総使用量（データ使用量＋アップロードファイル使用量）を算出してフォーマット済みの総使用量を返す
     *
     * @param string $tables_size
     * @param string $uploads_size
     * @return string
     */
    private static function calculateTotalUsage(string $tables_size, string $uploads_size): string
    {
        try {
            $tables_bytes = self::parseFormattedSize($tables_size);
            $uploads_bytes = self::parseFormattedSize($uploads_size);
            
            $total_bytes = $tables_bytes + $uploads_bytes;
            
            return FileUtils::getFormatSizeDecimalPoint((int)$total_bytes);
        } catch (\Exception $e) {
            Log::error('StorageUsageCalculator: Total usage calculation failed', ['error' => $e->getMessage()]);
            return '0B';
        }
    }

    /**
     * フォーマット済みのプラン容量を返す ※env設定がない場合、数値以外の設定はnullを返す
     *
     * @return string|null
     */
    private static function getPlanLimitFormatted(): ?string
    {
        $storage_limit_mb = env('STORAGE_LIMIT_MB');
        
        // env設定がない場合はnullを返す
        if (empty($storage_limit_mb) || !is_numeric($storage_limit_mb)) {
            return null;
        }
        
        // MB→バイトに変換してからフォーマット
        $storage_limit_bytes = intval($storage_limit_mb) * 1024 * 1024;
        
        return FileUtils::getFormatSizeDecimalPoint($storage_limit_bytes);
    }

    /**
     * 使用率を計算して返す ※プラン容量が設定されていない場合はnullを返す
     *
     * @param string $total_usage フォーマット済み総使用量
     * @param string|null $plan_limit フォーマット済みプラン容量
     * @return float|null 使用率（パーセンテージではない）
     */
    private static function getUsagePercentage(string $total_usage, ?string $plan_limit): ?float
    {
        // プラン容量が設定されていない場合はnullを返す
        if (empty($plan_limit)) {
            return null;
        }
        
        try {
            $total_bytes = self::parseFormattedSize($total_usage);
            $limit_bytes = self::parseFormattedSize($plan_limit);
            
            // プラン容量が0の場合は0%として扱う
            if ($limit_bytes <= 0) {
                return 0.0;
            }
            
            // 使用率を計算（0.0以上の値、どのぐらい超過しているかも緊急度の判断基準になる為、100%超過も表示）
            $percentage = $total_bytes / $limit_bytes;
            
            return $percentage;
            
        } catch (\Exception $e) {
            Log::error('StorageUsageCalculator: Usage percentage calculation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 警告表示が必要かどうかを判定
     *
     * @param float|null $usage_percentage 使用率
     * @return bool 警告表示が必要な場合true
     */
    public static function shouldShowWarning(?float $usage_percentage): bool
    {
        // 使用率が取得できない場合は警告しない
        if ($usage_percentage === null) {
            return false;
        }
        
        // 警告閾値を取得（デフォルト80%）
        $warning_threshold = env('STORAGE_USAGE_WARNING_THRESHOLD', 0.8);
        
        // 使用率が閾値以上の場合は警告表示
        return $usage_percentage >= $warning_threshold;
    }

    /**
     * 後続処理の計算用にフォーマット済みサイズ文字列をバイト数に変換して返す
     *
     * @param string $formatted_size
     * @return int
     */
    private static function parseFormattedSize(string $formatted_size): int
    {
        // 前後の空白を除去（例：" 177.50MB " → "177.50MB"）
        $formatted_size = trim($formatted_size);
        
        // 単位とその倍数を定義（長い単位から順番に処理するため降順で配列を構成）※「MB」→「B」の順でチェックしないと「177.50MB」が「B」として誤認識される
        $units = ['TB' => 1024*1024*1024*1024, 'GB' => 1024*1024*1024, 'MB' => 1024*1024, 'KB' => 1024, 'B' => 1];
        
        // 各単位をチェックして該当する単位を見つける
        foreach ($units as $unit => $multiplier) {
            // 文字列の末尾が単位文字列と一致するかチェック ※例：「177.50MB」の末尾2文字が「MB」と一致するか
            if (substr($formatted_size, -strlen($unit)) === $unit) {
                // 単位部分を除去して数値部分のみを取得 ※例：「177.50MB」→「177.50」
                $number = floatval(str_replace($unit, '', $formatted_size));
                // 数値に倍数を掛けてバイト数に変換（例：177.50 * 1048576 = 186122240バイト）
                return intval($number * $multiplier);
            }
        }
        
        // どの単位にも一致しなかった場合は0バイトを返す
        return 0;
    }
}