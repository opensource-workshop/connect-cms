<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 施設予約のフレーム設定項目
 */
final class ReservationFrameConfig extends EnumsBase
{
    // 定数メンバ
    const calendar_initial_display_type = 'calendar_initial_display_type';
    const facility_display_type = 'facility_display_type';
    const initial_facility = 'initial_facility';

    // key/valueの連想配列
    const enum = [
        self::calendar_initial_display_type => 'カレンダー初期表示',
        self::facility_display_type => '施設表示',
        self::initial_facility => '初期表示する施設',
    ];
}
