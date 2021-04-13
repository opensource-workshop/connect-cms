<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

class Learningtasks extends Model
{
    // [TODO] delated_atカラムはあるが、下記は指定されてなかった。joinなどで使ってるので、delated_at is null の修正が必要。
    // 論理削除
    // use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

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
     * レポート使用有無の文字列表記
     */
    public function strUseReport()
    {
        if ($this->use_report == 1) {
            return "使用する";
        }
        return "使用しない";
    }
}
