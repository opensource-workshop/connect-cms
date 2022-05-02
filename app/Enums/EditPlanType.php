<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 予定編集区分
 */
final class EditPlanType extends EnumsBase
{
    // 定数メンバ
    const only = 'only';
    const after = 'after';
    const all = 'all';

    // self::only => 'この予定のみ変更する',
    // self::after => 'この日付以降を変更する',
    // self::all => '全ての予定を変更する',
}
