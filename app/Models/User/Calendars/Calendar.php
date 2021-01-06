<?php

namespace App\Models\User\Calendars;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

/**
 * カレンダー・バケツ
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダー・プラグイン
 * @package モデル
 */
class Calendar extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'name'];
}
