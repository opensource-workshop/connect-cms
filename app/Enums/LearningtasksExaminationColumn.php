<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 課題管理の試験日のカラム
 */
final class LearningtasksExaminationColumn extends EnumsBase
{
    // 定数メンバ
    const id = 'id';
    const post_id = 'post_id';
    const start_at = 'start_at';
    const end_at = 'end_at';
    const entry_end_at = 'entry_end_at';

    // key/valueの連想配列
    const enum = [
        self::id => 'id',
        self::post_id => 'post_id',
        self::start_at => '試験開始日時',
        self::end_at => '試験終了日時',
        self::entry_end_at => '申込終了日時',
    ];

    /**
     * インポート のkey/valueの連想配列を返す
     */
    public static function getImportColumn()
    {
        $code_columns = static::enum;

        // エクスポートに不要な項目 を取り除く
        unset($code_columns[self::post_id]);
        return $code_columns;
    }
}
