<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 課題管理の使用機能
 */
final class LearningtaskUseFunction extends EnumsBase
{
    // 定数メンバ
    const use_need_auth = 'use_need_auth';

    // --- レポート設定
    // 利用するレポート提出機能
    const use_report = 'use_report';
    const use_report_evaluate = 'use_report_evaluate';
    const use_report_reference = 'use_report_reference';
    // 利用する提出機能
    const use_report_file = 'use_report_file';
    const use_report_comment = 'use_report_comment';
    const use_report_mail = 'use_report_mail';
    // 利用する評価機能
    const use_report_evaluate_file = 'use_report_evaluate_file';
    const use_report_evaluate_comment = 'use_report_evaluate_comment';
    const use_report_evaluate_mail = 'use_report_evaluate_mail';
    // 利用する教員から参考資料機能
    const use_report_reference_file = 'use_report_reference_file';
    const use_report_reference_comment = 'use_report_reference_comment';
    const use_report_reference_mail = 'use_report_reference_mail';
    // 表示方法
    const use_report_status_collapse = 'use_report_status_collapse';

    // --- 試験設定
    // 利用するレポート試験機能
    const use_examination = 'use_examination';
    const use_examination_evaluate = 'use_examination_evaluate';
    const use_examination_reference = 'use_examination_reference';
    // 利用する提出機能
    const use_examination_file = 'use_examination_file';
    const use_examination_comment = 'use_examination_comment';
    const use_examination_mail = 'use_examination_mail';
    // 利用する評価機能
    const use_examination_evaluate_file = 'use_examination_evaluate_file';
    const use_examination_evaluate_comment = 'use_examination_evaluate_comment';
    const use_examination_evaluate_mail = 'use_examination_evaluate_mail';
    // 利用する教員から参考資料機能
    const use_examination_reference_file = 'use_examination_reference_file';
    const use_examination_reference_comment = 'use_examination_reference_comment';
    const use_examination_reference_mail = 'use_examination_reference_mail';
    // 表示方法
    const use_examination_status_collapse = 'use_examination_status_collapse';

    // --- 総合評価設定
    // 利用する総合評価機能
    const use_evaluate = 'use_evaluate';
    // 利用する提出機能
    const use_evaluate_file = 'use_evaluate_file';
    const use_evaluate_comment = 'use_evaluate_comment';
    const use_evaluate_mail = 'use_evaluate_mail';

    // key/valueの連想配列
    const enum = [
        self::use_need_auth => 'ログインの要否',
        // --- レポート設定
        // 利用するレポート提出機能
        self::use_report => '提出',
        self::use_report_evaluate => '評価',
        self::use_report_reference => '教員から参考資料',
        // 利用する提出機能
        self::use_report_file => 'アップロード',
        self::use_report_comment => '本文入力',
        self::use_report_mail => 'メール送信（教員宛）',
        // 利用する評価機能
        self::use_report_evaluate_file => 'アップロード',
        self::use_report_evaluate_comment => 'コメント入力',
        self::use_report_evaluate_mail => 'メール送信（受講者宛）',
        // 利用する教員から参考資料機能
        self::use_report_reference_file => 'アップロード',
        self::use_report_reference_comment => 'コメント入力',
        self::use_report_reference_mail => 'メール送信（受講者宛）',
        // 表示方法
        self::use_report_status_collapse => '履歴を開閉する',
        // --- 試験設定
        // 利用するレポート提出機能
        self::use_examination => '提出',
        self::use_examination_evaluate => '評価',
        self::use_examination_reference => '教員から参考資料',
        // 利用する提出機能
        self::use_examination_file => 'アップロード',
        self::use_examination_comment => '本文入力',
        self::use_examination_mail => 'メール送信（教員宛）',
        // 利用する評価機能
        self::use_examination_evaluate_file => 'アップロード',
        self::use_examination_evaluate_comment => 'コメント入力',
        self::use_examination_evaluate_mail => 'メール送信（受講者宛）',
        // 利用する教員から参考資料機能
        self::use_examination_reference_file => 'アップロード',
        self::use_examination_reference_comment => 'コメント入力',
        self::use_examination_reference_mail => 'メール送信（受講者宛）',
        // 表示方法
        self::use_examination_status_collapse => '履歴を開閉する',
        // --- 総合評価設定
        // 利用する総合評価機能
        self::use_evaluate => '評価',
        // 利用する提出機能
        self::use_evaluate_file => 'アップロード',
        self::use_evaluate_comment => 'コメント入力',
        self::use_evaluate_mail => 'メール送信（受講者宛）',
    ];
}
