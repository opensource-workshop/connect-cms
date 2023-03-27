<?php

namespace App\Enums;

/**
 * マニュアル・カテゴリ
 */
final class ManualCategory extends EnumsBase
{
    // 定数メンバ
    const top = 'top';
    const blueprint = 'blueprint';
    const common = 'common';
    const manage = 'manage';
    const manage_data = 'manage_data';
    const user = 'user';
    const mypage = 'mypage';
    const study = 'study';
    const error = 'error';
    const usage = 'usage';

    // key/valueの連想配列
    const enum = [
        self::top => 'トップ',
        self::blueprint => '設計',
        self::common => '共通機能',
        self::manage => '管理者',
        self::manage_data => '管理者(データ管理)',
        self::user => '一般ユーザ',
        self::mypage => 'マイページ',
        self::study => 'Connect-Study',
        self::error => 'エラー説明',
        self::usage => '逆引き',
    ];

    // カテゴリごとの音声（mix版を指定した場合）の連想配列
    // ここでは小文字で設定（ディレクトリは小文字で作成）、pollyを呼ぶ際は先頭大文字に変換している。
    const voice_id = [
        self::top => 'takumi',
        self::blueprint => 'takumi',
        self::common => 'mizuki',
        self::manage => 'takumi',
        self::manage_data => 'takumi',
        self::user => 'kazuha',
        self::mypage => 'tomoko',
        self::study => 'tomoko',
        self::error => 'takumi',
        self::usage => 'kazuha',
    ];

    // Voiceエンジン
    const voice_engine = [
        self::top => 'neural',
        self::blueprint => 'neural',
        self::common => 'standard',
        self::manage => 'neural',
        self::manage_data => 'neural',
        self::user => 'neural',
        self::mypage => 'neural',
        self::study => 'neural',
        self::error => 'neural',
        self::usage => 'neural',
    ];

    /**
     * 対応したvoice_idを返す
     */
    public static function getVoiceId($key): string
    {
        return static::voice_id[$key];
    }

    /**
     * 対応したvoice_engineを返す
     */
    public static function getVoiceEngine($key): string
    {
        return static::voice_engine[$key];
    }
}
