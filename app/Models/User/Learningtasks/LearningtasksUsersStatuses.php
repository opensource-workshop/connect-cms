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
     * ステータス毎の文言
     */
    protected $status_str = [
        // レポートの課題提出
        1 => ['ststus_name'      => '提出',
              'post_time_name'   => '提出日時',
              'upload_file_name' => '提出ファイル',
              'has_file'         => true,
              'has_examination'  => false,
              'has_grade'        => false,
              'has_comment'      => false],
        // レポートの評価
        2 => ['ststus_name'      => '評価',
              'post_time_name'   => '評価日時',
              'upload_file_name' => '添削・参考ファイル',
              'has_file'         => true,
              'has_examination'  => false,
              'has_grade'        => true,
              'has_comment'      => true],
        // レポートのコメント
        3 => ['ststus_name'      => '教員からのコメント',
              'post_time_name'   => '記載日時',
              'upload_file_name' => '参考ファイル',
              'has_file'         => true,
              'has_examination'  => false,
              'has_grade'        => false,
              'has_comment'      => true],
        // 試験申し込み
        4 => ['ststus_name'      => '申し込み',
              'post_time_name'   => '申し込み日時',
              'upload_file_name' => '',
              'has_file'         => false,
              'has_examination'  => true,
              'has_grade'        => false,
              'has_comment'      => false],
        // 試験の解答提出
        5 => ['ststus_name'      => '解答提出',
              'post_time_name'   => '解答日時',
              'upload_file_name' => '解答ファイル',
              'has_file'         => true,
              'has_examination'  => false,
              'has_grade'        => false,
              'has_comment'      => false],
        // 試験の評価
        6 => ['ststus_name'      => '評価',
              'post_time_name'   => '評価日時',
              'upload_file_name' => '添削・参考ファイル',
              'has_file'         => true,
              'has_examination'  => false,
              'has_grade'        => true,
              'has_comment'      => true],
        // 試験のコメント
        7 => ['ststus_name'      => '教員からのコメント',
              'post_time_name'   => '記載日時',
              'upload_file_name' => '参考ファイル',
              'has_file'         => true,
              'has_examination'  => false,
              'has_grade'        => false,
              'has_comment'      => true],
    ];

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
        if (array_key_exists($this->task_status, $this->status_str)) {
            return $re_str . $this->status_str[$this->task_status]['ststus_name'];
        }
        return "";
    }

    /**
     * 日時の文言の取得
     */
    public function getStstusPostTimeName()
    {
        if (array_key_exists($this->task_status, $this->status_str)) {
            return $this->status_str[$this->task_status]['post_time_name'];
        }
        return "";
    }

    /**
     * ファイル文言の取得
     */
    public function getUploadFileName()
    {
        if (array_key_exists($this->task_status, $this->status_str)) {
            return $this->status_str[$this->task_status]['upload_file_name'];
        }
        return "";
    }

    /**
     * 評価を持つステータスか
     */
    public function hasGrade()
    {
        if (array_key_exists($this->task_status, $this->status_str)) {
            return $this->status_str[$this->task_status]['has_grade'];
        }
        return false;
    }

    /**
     * コメントを持つステータスか
     */
    public function hasComment()
    {
        if (array_key_exists($this->task_status, $this->status_str)) {
            return $this->status_str[$this->task_status]['has_comment'];
        }
        return false;
    }

    /**
     * ファイルを持つステータスか
     */
    public function hasFile()
    {
        if (array_key_exists($this->task_status, $this->status_str)) {
            return $this->status_str[$this->task_status]['has_file'];
        }
        return false;
    }

    /**
     * 試験開始日を持つステータスか
     */
    public function hasExamination()
    {
        if (array_key_exists($this->task_status, $this->status_str)) {
            return $this->status_str[$this->task_status]['has_examination'];
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
