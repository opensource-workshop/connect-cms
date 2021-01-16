<?php

namespace App\Models\User\Calendars;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

use App\Userable;

/**
 * カレンダー・記事
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダー・プラグイン
 * @package モデル
 */
class CalendarPost extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['calendar_id', 'allday_flag', 'start_date', 'start_time', 'end_date', 'end_time', 'title', 'body'];

    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['start_date', 'end_date'];

    /**
     * 新しいEloqunetコレクションインスタンスの生成
     * カレンダーPOST 用のCollection を使用する。
     */
    public function newCollection(array $models = [])
    {
        return new CalendarPosts($models);
    }

    /**
     * 全日予定フラグ
     */
    public function setAlldayFlagAttribute($value)
    {
        if ($value == '1') {
            $this->attributes['allday_flag'] = 1;
        } else {
            $this->attributes['allday_flag'] = 0;
        }
    }

    /**
     * 開始日の取得
     * アクセサを用いて取得しなければ、dateFormat の形式が反映されないためアクセサを作成
     */
    public function getStartDateAttribute($value)
    {
        if (array_key_exists('start_date', $this->attributes)) {
            return $this->attributes['start_date'];
        }
        return "";
    }

    /**
     * 開始日の設定
     */
    public function setStartDateAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['start_date'] = date('Ymd');
        } else {
            $this->attributes['start_date'] = $value;
        }
    }

    /**
     * 開始時間の設定
     */
    public function setStartTimeAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['start_time'] = '00:00:00';
        } else {
            $this->attributes['start_time'] = $value;
        }
    }

    /**
     * 終了日の取得
     * アクセサを用いて取得しなければ、dateFormat の形式が反映されないためアクセサを作成
     */
    public function getEndDateAttribute($value)
    {
        if (array_key_exists('end_date', $this->attributes)) {
            return $this->attributes['end_date'];
        }
        return "";
    }

    /**
     * 終了日の設定
     */
    public function setEndDateAttribute($value)
    {
        if (empty($value)) {
            if (!empty($this->start_date)) {
                $this->attributes['end_date'] = $this->start_date;
            } else {
                $this->attributes['end_date'] = date('Ymd');
            }
        } else {
            $this->attributes['end_date'] = $value;
        }
    }

    /**
     * 終了時間の設定
     */
    public function setEndTimeAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['end_time'] = '00:00:00';
        } else {
            $this->attributes['end_time'] = $value;
        }
    }

    /**
     * 全日予定の取得
     */
    public function getAlldayFlagString()
    {
        if (empty($this->allday_flag)) {
            return "OFF";
        } else {
            return "ON";
        }
    }

    /**
     * 開始日時の取得
     */
    public function getStartDateTime()
    {
        if (empty($this->start_time)) {
            return $this->start_date;
        } else {
            return $this->start_date . " " . $this->getStartTime();
        }
    }

    /**
     * 終了日時の取得
     */
    public function getEndDateTime()
    {
        if (empty($this->end_time)) {
            return $this->end_date;
        } else {
            return $this->end_date . " " . $this->getEndTime();
        }
    }

    /**
     * 開始時間の取得
     */
    public function getStartTime($y_m_d = null)
    {
        if ($this->allday_flag == 1) {
            return "";
        }
        if (!empty($y_m_d) && $y_m_d != $this->start_date) {
            return "前日";
        }
        if (empty($this->start_time)) {
            return "";
        } else {
            return substr($this->start_time, 0, 5);
        }
    }

    /**
     * 終了時間の取得
     */
    public function getEndTime($y_m_d = null)
    {
        if ($this->allday_flag == 1) {
            return "";
        }
        if (!empty($y_m_d) && $y_m_d != $this->end_date) {
            return "翌日";
        }
        if (empty($this->end_time)) {
            return "";
        } else {
            return substr($this->end_time, 0, 5);
        }
    }

    /**
     * status 文字列の取得
     */
    public function getStatusBadge($need_action_only = false)
    {
        if ($this->status === null && !$need_action_only) {
            return '<span class="badge badge-info align-bottom">新規</span>';
        } elseif ($this->status == 0 && !$need_action_only) {
            return '<span class="badge badge-info align-bottom">公開中</span>';
        } elseif ($this->status == 1) {
            return '<span class="badge badge-warning align-bottom">一時保存</span>';
        } elseif ($this->status == 2) {
            return '<span class="badge badge-warning align-bottom">承認待ち</span>';
        }
        return "";
    }
}
