<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * フォームの閲覧制限タイプ
 */
final class FormAccessLimitType extends EnumsBase
{
    // 定数メンバ
    /** 制限しない */
    const none = 0;
    /** パスワードで閲覧制限する */
    const password = 1;
    /** 画像認証で閲覧制限する */
    const captcha = 2;
    /** フォーム送信時に画像認証する */
    const captcha_form_submit = 3;

    /** key/valueの連想配列 */
    const enum = [
        self::none => '制限しない',
        self::password => 'パスワードで閲覧制限する',
        self::captcha => '画像認証で閲覧制限する',
        self::captcha_form_submit => 'フォーム送信時に画像認証する',
    ];

    /**
     * 初期値
     */
    public static function getDefault()
    {
        return self::none;
    }
}
