<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

use App\Models\Common\Uploads;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Userable;

class LearningtasksUsersStatuses extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['post_id', 'user_id', 'task_status', 'comment', 'upload_id', 'examination_id', 'grade'];

    /**
     * ステータス文言の取得
     */
    public function getStstusName()
    {
        // "再"提出や"再"評価の判断。
        // 処理するデータ以前に、同じステータスのものがあれば、"再"と判断できる。
        $status_count = LearningtasksUsersStatuses::where('post_id', $this->post_id)
                                                  ->where('task_status', $this->task_status)
                                                  ->where('id', '<', $this->id)
                                                  ->orderBy('id', 'asc')
                                                  ->count();
        $re_str = '';
        if ($status_count > 0) {
            $re_str = '再';
        }

        if ($this->task_status == 1) {
            return $re_str . "提出";
        } elseif ($this->task_status == 2) {
            return $re_str . "評価";
        } elseif ($this->task_status == 3) {
            return $re_str . "申し込み";
        } elseif ($this->task_status == 4) {
            return $re_str . "提出";
        } elseif ($this->task_status == 5) {
            return $re_str . "評価";
        } elseif ($this->task_status == 9) {
            return "教員からのコメント";
        }
        return "";
    }

    /**
     * 日時の文言の取得
     */
    public function getStstusPostTimeName()
    {
        if ($this->task_status == 1) {
            return "提出日時";
        } elseif ($this->task_status == 2) {
            return "評価日時";
        } elseif ($this->task_status == 3) {
            return "申し込み日時";
        } elseif ($this->task_status == 4) {
            return "提出日時";
        } elseif ($this->task_status == 5) {
            return "評価日時";
        } elseif ($this->task_status == 9) {
            return "記載日時";
        }
        return "";
    }

    /**
     * ファイル文言の取得
     */
    public function getUploadFileName()
    {
        if ($this->task_status == 1) {
            return "提出ファイル";
        } elseif ($this->task_status == 2) {
            return "添削・参考ファイル";
        } elseif ($this->task_status == 3) {
            return "";
        } elseif ($this->task_status == 4) {
            return "提出ファイル";
        } elseif ($this->task_status == 5) {
            return "添削・参考ファイル";
        } elseif ($this->task_status == 9) {
            return "参考ファイル";
        }
        return "";
    }

    /**
     * 評価を持つステータスか
     */
    public function hasGrade()
    {
        if ($this->task_status == 1) {
            return false;
        } elseif ($this->task_status == 2) {
            return true;
        } elseif ($this->task_status == 3) {
            return false;
        } elseif ($this->task_status == 4) {
            return false;
        } elseif ($this->task_status == 5) {
            return true;
        } elseif ($this->task_status == 9) {
            return false;
        }
        return false;
    }

    /**
     * コメントを持つステータスか
     */
    public function hasComment()
    {
        if ($this->task_status == 1) {
            return false;
        } elseif ($this->task_status == 2) {
            return true;
        } elseif ($this->task_status == 3) {
            return false;
        } elseif ($this->task_status == 4) {
            return false;
        } elseif ($this->task_status == 5) {
            return true;
        } elseif ($this->task_status == 9) {
            return true;
        }
        return false;
    }

    /**
     * アップロードファイル名称
     */
    public function upload()
    {
        // uploadsテーブルをこのレコードから見て 1:1 で紐づけ
        // キーは指定しておく。Uploads の id にこのレコードの upload_id を紐づける。
        // withDefault() を指定しておくことで、Uploads がないときに空のオブジェクトが返ってくるので、null po 防止。
        return $this->hasOne(Uploads::class, 'id', 'upload_id')->withDefault();
    }
}
