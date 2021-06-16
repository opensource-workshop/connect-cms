<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

class LearningtasksPosts extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // Carbonインスタンス（日付）に自動的に変換
    protected $dates = ['posted_at'];

    /**
     * リスト表示用タイトル
     * 改行を取り除いたもの。
     */
    public function getNobrPostTitle()
    {
        return str_ireplace(['<br>', '<br />'], ' ', $this->post_title);
    }
}
