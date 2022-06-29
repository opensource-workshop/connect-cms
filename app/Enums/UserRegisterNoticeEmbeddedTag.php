<?php

namespace App\Enums;

use App\Enums\NoticeEmbeddedTag;

/**
 * ユーザ本登録の通知の埋め込みタグ
 */
final class UserRegisterNoticeEmbeddedTag extends NoticeEmbeddedTag
{
    // 定数メンバ
    const user_name = 'user_name';
    const login_id = 'login_id';
    const initial_password = 'initial_password';
    const email = 'email';
    const user_register_requre_privacy = 'user_register_requre_privacy';
    const to_datetime = 'to_datetime';

    // key/valueの連想配列
    const enum = [
        self::site_name => 'サイト名',
        self::body => '本文（ユーザ名, ログインID, eメールアドレス, 項目設定の追加項目, 個人情報保護方針への同意 の全てを含む）',
        self::user_name => 'ユーザ名',
        self::login_id => 'ログインID',
        self::initial_password => '初期パスワード',
        self::email => 'eメールアドレス',
        self::user_register_requre_privacy => '個人情報保護方針への同意',
        self::to_datetime => '登録日時',
    ];

    /**
     * 埋め込みタグの説明を取得
     */
    public static function getDescriptionEmbeddedTags(bool $use_title = false, bool $use_body = false): array
    {
        // 埋め込みタグ, 内容
        $embedded_tags[] = ['[[' . self::site_name . ']]', self::getDescription(self::site_name)];
        $embedded_tags[] = ['[[' . self::body . ']]', self::getDescription(self::body)];
        $embedded_tags[] = ['[[' . self::user_name . ']]', self::getDescription(self::user_name)];
        $embedded_tags[] = ['[[' . self::login_id . ']]', self::getDescription(self::login_id)];
        $embedded_tags[] = ['[[' . self::initial_password . ']]', self::getDescription(self::initial_password)];
        $embedded_tags[] = ['[[' . self::email . ']]', self::getDescription(self::email)];
        $embedded_tags[] = ['[[' . self::user_register_requre_privacy . ']]', self::getDescription(self::user_register_requre_privacy)];
        $embedded_tags[] = ['[[' . self::to_datetime . ']]', self::getDescription(self::to_datetime)];
        return $embedded_tags;
    }
}
