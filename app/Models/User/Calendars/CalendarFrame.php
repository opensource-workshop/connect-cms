<?php

namespace App\Models\User\Calendars;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

/**
 * カレンダー・フレーム
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダー・プラグイン
 * @package モデル
 */
class CalendarFrame extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['calendar_id', 'frame_id', 'view_format'];
}
