<?php

namespace App\Models\User\Bbses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kalnoy\Nestedset\NodeTrait;

use App\Userable;

class BbsPost extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['bbs_id', 'title', 'body', 'thread_root_id', 'thread_updated_at', 'temporary_flag', '_lft', '_rgt', 'parent_id'];

    // 入れ子集合モデル
    use NodeTrait;

    /**
     * Scope の設定。root_id 単位での入れ子集合モデルを設定することで、スレッド内での入れ子集合モデルとする。
     * スレッド内での入れ子集合モデルとすることで、不要なデータベース更新を減らしトラブルを軽減する。
     * 根記事の取得は parent_id = null で取得可能
     */
    protected function getScopeAttributes()
    {
        return ['thread_root_id'];
    }

    /**
     * 返信用のインデント付き本文の取得
     */
    public function getReplyBody()
    {
        $reply_body = str_ireplace("<p>", "<p>&gt; ", $this->body);
        $reply_body = str_ireplace("<br />", "<br />&gt; ", $reply_body);

        return "<p></p>" . $reply_body;
    }

    /**
     * 返信用のタイトル
     */
    public function getReplyTitle()
    {
        // Re{0-9}: <- Re で始まり、: がある。に合致しない場合は、"Re: " + タイトル
        // Re で始まり、: がある。間に数字がない場合は、"Re2: " + タイトル
        // Re で始まり、: がある。間に数字がある場合は、"Re{数字++}: " + タイトル
        if (stripos($this->title, "Re") === 0 && stripos($this->title, ":") !== false) {
            // : までを抜き出す。
            $title_head = mb_substr($this->title, 0, stripos($this->title, ":"));
            // : までの文字から数字のみ抽出
            $reply_no = preg_replace('/[^0-9]/', '', $title_head);
            if (is_numeric($reply_no)) {
                $reply_no++;
                $return_title = "Re" . $reply_no . ": " . mb_substr($this->title, stripos($this->title, ":"));
            } else {
                $return_title = "Re2: " . mb_substr($this->title, stripos($this->title, ":") + 1);
            }
        } else {
            $return_title = "Re: " . $this->title;
        }
        return $return_title;
    }
}
