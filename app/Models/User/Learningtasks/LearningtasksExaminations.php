<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\DayOfWeek;
use App\UserableNohistory;
use App\Enums\LearningtasksExaminationColumn;

class LearningtasksExaminations extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = [
        'start_at',
        'end_at',
        'entry_end_at',
    ];

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'post_id',
        'start_at',
        'end_at',
        'entry_end_at',
    ];

    /**
     * 日付カラムか
     */
    public static function isDateColumn($column_type)
    {
        // 日付カラム
        if ($column_type == LearningtasksExaminationColumn::start_at ||
                $column_type == LearningtasksExaminationColumn::end_at ||
                $column_type == LearningtasksExaminationColumn::entry_end_at) {
            return true;
        }
        return false;
    }

    /**
     * 試験日の画面表記を取得
     */
//    public function getViewDate()
//    {
//        // 判定に必要な値の準備
//        $start_ts      = strtotime($this->start_at);
//        $start_ym_jp   = date('Y年m月d日', $start_ts);
//        $start_week_no = date('w', $start_ts);
//        $start_week_jp = DayOfWeek::getDescription($start_week_no);
//        $start_hs      = date('H時i分', $start_ts);
//
//        $end_ts        = strtotime($this->end_at);
//        $end_ym_jp     = date('Y年m月d日', $end_ts);
//        $end_week_no   = date('w', $end_ts);
//        $end_week_jp   = DayOfWeek::getDescription($end_week_no);
//        $end_hs        = date('H時i分', $end_ts);
//
//        // 開始日時
//        $start = $start_ym_jp . '(' . $start_week_jp . ') ' . $start_hs;
//
//        // 開始日と終了日が同じか判定
//        $end = '';
//        if ($start_ym_jp != $end_ym_jp) {
//            $end .= $end_ym_jp . '(' . $end_week_jp . ') ';
//        }
//        $end .= $end_hs;
//
//        return $start . " - " . $end;
//    }
}
