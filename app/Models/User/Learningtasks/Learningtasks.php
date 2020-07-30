<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;

class Learningtasks extends Model
{
    /**
     * レポート提出機能の使用有無
     */
    public function useReport()
    {
        if ($this->use_report == 1) {
            return true;
        }
        return false;
    }

    /**
     * レポート試験機能の使用有無
     */
    public function useExamination()
    {
        if ($this->use_examination == 1) {
            return true;
        }
        return false;
    }


    /**
     *  レポート使用有無の文字列表記
     */
    public function strUseReport()
    {
        if ($this->use_report == 1) {
            return "使用する";
        }
        return "使用しない";
    }
}
