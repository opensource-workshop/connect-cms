<?php

namespace App\Models\Common;

use Carbon\Carbon;

class ConnectCarbon extends Carbon
{
    /**
     * 独自祝日データ（追加プロパティ）
     */
    public $holiday = null;

    /**
     *  祝日受け取り
     */
    public function setHoliday($holiday)
    {
        $this->holiday = $holiday;
    }

    /**
     *  祝日取得
     */
    public function getHoliday()
    {
        if ($this->hasHoliday()) {
            return $this->holiday;
        }
        return null;
    }

    /**
     *  祝日名取得
     */
    public function getHolidayName()
    {
        if ($this->hasHoliday()) {
            return $this->holiday->getName();
        }
        return "";
    }

    /**
     *  祝日判断
     */
    public function hasHoliday()
    {
        if (empty($this->holiday)) {
            return false;
        }
        if (stripos(get_class($this->holiday), "YasumiHoliday") === false) {
            return true;
        } elseif ($this->holiday->orginal_holiday_status == 1) {
            return true;
        }
        return false;
    }
}
