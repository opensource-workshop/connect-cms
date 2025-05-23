<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;
use App\Enums\LearningtaskUseFunction;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LearningtasksUseSettings extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use HasFactory;

    // create()やupdate()で入力を受け付ける ホワイトリスト
    protected $fillable = [
        'learningtasks_id',
        'post_id',
        'use_function',
        'value',
        'datetime_value',
    ];

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'datetime_value' => 'datetime',
    ];

    /**
     * 日時を使う機能か
     */
    public static function isDatetimeUseFunction($use_function)
    {
        // 日時
        if ($use_function == LearningtaskUseFunction::report_end_at) {
            return true;
        }
        return false;
    }
}
