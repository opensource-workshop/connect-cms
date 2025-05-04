<?php

namespace App\Models\User\Faqs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class FaqsPosts extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'posted_at' => 'datetime',
    ];

    // 更新する項目の定義
    protected $fillable = ['contents_id', 'faqs_id', 'post_title', 'post_text', 'categories_id', 'posted_at', 'display_sequence', 'status'];

    /**
     *  リスト表示用タイトル
     *  改行を取り除いたもの。
     */
    public function getNobrPostTitle()
    {
        return str_ireplace(['<br>', '<br />'], ' ', $this->post_title);
    }
}
