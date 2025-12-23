<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * データベースのフレーム設定項目
 */
final class DatabaseFrameConfig extends EnumsBase
{
    // 定数メンバ
    const database_use_select_multiple_flag = 'database_use_select_multiple_flag';
    const database_show_trend_words = 'database_show_trend_words';
    const database_trend_words = 'database_trend_words';
    const database_trend_words_caption = 'database_trend_words_caption';
    const database_destination_frame = 'database_destination_frame';
    const database_view_count_spectator = 'database_view_count_spectator';
    const database_page_total_views = 'database_page_total_views';
    const database_show_like_list = 'database_show_like_list';
    const database_show_like_detail = 'database_show_like_detail';

    // key/valueの連想配列
    const enum = [
        self::database_use_select_multiple_flag => '絞り込み機能の表示（複数選択）',
        self::database_show_trend_words => '急上昇ワードの表示',
        self::database_trend_words => '急上昇ワード',
        self::database_trend_words_caption => '急上昇ワード表示項目名',
        self::database_destination_frame => '検索後の遷移先',
        self::database_view_count_spectator => '表示件数リストの表示',
        self::database_page_total_views => '表示件数の表示',
        self::database_show_like_list => 'いいねボタンの表示（一覧）',
        self::database_show_like_detail => 'いいねボタンの表示（詳細）',
    ];
}
