<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * プラグイン名
 */
final class PluginName extends EnumsBase
{
    // 定数メンバ
    const blogs = 'Blogs';
    const contents = 'Contents';
    const forms = 'Forms';
    const menus = 'Menus';
    const databases = 'Databases';
    const reservations = 'Reservations';
    const whatsnews = 'Whatsnews';
    const opacs = 'Opacs';
    const openingcalendars = 'Openingcalendars';
    const bbses = 'Bbses';
    const calendars = 'Calendars';
    const conventions = 'Conventions';
    const faqs = 'Faqs';
    const databasesearches = 'Databasesearches';
    const learningtasks = 'Learningtasks';
    const searchs = 'Searchs';
    const tabs = 'Tabs';
    const themechangers = 'Themechangers';
    const receives = 'Receives';
    const linklists = 'Linklists';

    // key/valueの連想配列
    const enum = [
        self::blogs => 'ブログ',
        self::contents => '固定記事',
        self::forms => 'フォーム',
        self::menus => 'メニュー',
        self::databases => 'データベース',
        self::reservations => '施設予約',
        self::whatsnews => '新着情報',
        self::openingcalendars => '開館カレンダー',
        self::bbses => '掲示板',
        self::calendars => 'カレンダー',
        self::conventions => 'イベント',
        self::databasesearches => 'データベース検索',
        self::faqs => 'FAQ',
        self::learningtasks => '課題管理',
        self::searchs => 'サイト内検索',
        self::tabs => 'タブ',
        self::themechangers => 'テーマチェンジャー',
        self::receives => 'データ収集',
        self::linklists => 'リンクリスト',
    ];

    /**
     * DBに登録される plugin_name を取得
     * @see resources\views\layouts\add_plugin.blade.php
     */
    public static function getPluginName($key): string
    {
        return strtolower($key);
    }
}
