<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 課題管理の使用機能
 */
final class LearningtaskUseFunction extends EnumsBase
{
    // 定数メンバ
    // レポート
    const report = 'report';
    // 試験
    const examination = 'examination';
    // 総合評価
    const evaluate = 'evaluate';

    // ログイン要否
    const use_need_auth = 'use_need_auth';

    // --- レポート設定(base)
    // [利用するレポート提出機能]
    const use_report = 'use_'.self::report;
    const use_report_evaluate = 'use_'.self::report.'_evaluate';
    const use_report_reference = 'use_'.self::report.'_reference';

    // [利用する提出機能]
    const use_report_file = 'use_'.self::report.'_file';
    const use_report_comment = 'use_'.self::report.'_comment';
    const use_report_mail = 'use_'.self::report.'_mail';
    const use_report_end = 'use_'.self::report.'_end';
    const report_end_at = 'report_end_at';
    // [利用する評価機能]
    const use_report_evaluate_file = 'use_'.self::report.'_evaluate_file';
    const use_report_evaluate_comment = 'use_'.self::report.'_evaluate_comment';
    const use_report_evaluate_mail = 'use_'.self::report.'_evaluate_mail';
    // [利用する教員から参考資料機能]
    const use_report_reference_file = 'use_'.self::report.'_reference_file';
    const use_report_reference_comment = 'use_'.self::report.'_reference_comment';
    const use_report_reference_mail = 'use_'.self::report.'_reference_mail';
    // [表示方法]
    const use_report_status_collapse = 'use_'.self::report.'_status_collapse';

    // --- 試験設定
    // [利用する試験提出機能]
    const use_examination = 'use_'.self::examination;
    const use_examination_evaluate = 'use_'.self::examination.'_evaluate';
    const use_examination_reference = 'use_'.self::examination.'_reference';
    // [利用する提出機能]
    const use_examination_file = 'use_'.self::examination.'_file';
    const use_examination_comment = 'use_'.self::examination.'_comment';
    const use_examination_mail = 'use_'.self::examination.'_mail';
    // [利用する評価機能]
    const use_examination_evaluate_file = 'use_'.self::examination.'_evaluate_file';
    const use_examination_evaluate_comment = 'use_'.self::examination.'_evaluate_comment';
    const use_examination_evaluate_mail = 'use_'.self::examination.'_evaluate_mail';
    // [利用する教員から参考資料機能]
    const use_examination_reference_file = 'use_'.self::examination.'_reference_file';
    const use_examination_reference_comment = 'use_'.self::examination.'_reference_comment';
    const use_examination_reference_mail = 'use_'.self::examination.'_reference_mail';
    // 表示方法
    const use_examination_status_collapse = 'use_'.self::examination.'_status_collapse';

    // --- 総合評価設定
    // [利用する総合評価機能]
    const use_evaluate = 'use_'.self::evaluate;
    // [利用する提出機能]
    const use_evaluate_file = 'use_'.self::evaluate.'_file';
    const use_evaluate_comment = 'use_'.self::evaluate.'_comment';
    const use_evaluate_mail = 'use_'.self::evaluate.'_mail';

    // ※ 下記ネーミングルールで 課題側(post)の設定を LearningtasksTool::checkFunction() で取得している
    //    例えば、'post_report_setting' は、'use_report_file' など、真ん中に report がないと機能しない。
    //
    // --- レポート設定(post)
    // 利用するレポート提出機能
    const post_report_setting = 'post_'.self::report.'_setting';
    // --- 試験設定(post)
    // 利用するレポート試験機能
    const post_examination_setting = 'post_'.self::examination.'_setting';
    // --- 総合評価設定(post)
    // 利用する総合評価機能
    const post_evaluate_setting = 'post_'.self::evaluate.'_setting';

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
        self::use_report_end => 'レポート提出終了日時で制御する',
        self::report_end_at => 'レポート提出終了日時',
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
        // 利用する試験提出機能
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

        // --- レポート設定(post)
        // 利用するレポート提出機能
        self::post_report_setting => 'レポート提出機能',
        // --- 試験設定(post)
        // 利用する試験提出機能
        self::post_examination_setting => '試験提出機能',
        // --- 総合評価設定(post)
        // 利用する総合評価機能
        self::post_evaluate_setting => '総合評価機能',
    ];
}
