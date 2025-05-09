<?php

namespace App\Models\User\Opacs;

use Illuminate\Database\Eloquent\Model;

class OpacsBooksLents extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'opacs_books_id',
        'lent_flag',
        'student_no',
        'return_scheduled',
        'return_date',
        'phone_no',
        'email',
        'lent_at',
        'delivery_request_flag',
        'delivery_request_date',
        'delivery_request_time',
    ];

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'lent_at' => 'datetime',
        'scheduled_return' => 'datetime',
        'delivery_request_date' => 'datetime',
    ];

    /**
     *  フォーマット付きの返却予定日を返却
     */
    public function getFormatRreturnScheduled()
    {
        // 本日のタイムスタンプ
        $ts_today = mktime(0, 0, 0);

        // 返却予定日のタイムスタンプ
        $ts_return_scheduled = strtotime($this->return_scheduled);

        // 返却予定日
        $ret_return_scheduled = $this->return_scheduled;
        if ($ts_today == $ts_return_scheduled) {
            $ret_return_scheduled = '<span class="cc-color-blue">' . $ret_return_scheduled . '</span>';
        } elseif ($ts_today > $ts_return_scheduled) {
            $ret_return_scheduled = '<span class="cc-color-red">' . $ret_return_scheduled . '</span>';
        }

        return $ret_return_scheduled;
    }

    /**
     *  借り区分の文字列を取得
     */
    public function getLentStr()
    {
        if ($this->lent_flag == 1) {
            return "貸出中";
        }
        if ($this->lent_flag == 2) {
            return "貸し出しリクエスト";
        }
        return "";
    }
}
