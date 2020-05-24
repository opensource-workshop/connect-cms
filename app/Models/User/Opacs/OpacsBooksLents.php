<?php

namespace App\Models\User\Opacs;

use Illuminate\Database\Eloquent\Model;

class OpacsBooksLents extends Model
{
    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['lent_at', 'scheduled_return'];

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
        }
        elseif ($ts_today > $ts_return_scheduled) {
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
