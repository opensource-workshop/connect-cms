<?php

namespace App\Models\User\Blogs;

use App\Enums\BlogFrameScope;
use App\Userable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class BlogsPosts extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 定数メンバ
    const read_more_button_default = '続きを読む';
    const close_more_button_default = '閉じる';

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'posted_at' => 'datetime',
    ];

    // 更新する項目の定義
    protected $fillable = [
        'contents_id',
        'blogs_id',
        'post_title',
        'post_text',
        'post_text2',
        'read_more_flag',
        'read_more_button',
        'close_more_button',
        'categories_id',
        'important',
        'status',
        'posted_at',
        'first_committed_at',
    ];

    /**
     * 表示条件に対する条件追加
     */
    public static function appendSettingWhere($query, $blog_frame)
    {
        // 全件表示
        if (empty($blog_frame->scope)) {
            // 全件取得のため、追加条件なしで戻る。
        } elseif ($blog_frame->scope == BlogFrameScope::year) {
            // 年
            $query->where('posted_at', '>=', $blog_frame->scope_value . '-01-01')
                  ->where('posted_at', '<=', $blog_frame->scope_value . '-12-31 23:59:59');
        } elseif ($blog_frame->scope == BlogFrameScope::fiscal) {
            // 年度
            $fiscal_next = intval($blog_frame->scope_value) + 1;
            $query->where('posted_at', '>=', $blog_frame->scope_value . '-04-01')
                  ->where('posted_at', '<=', $fiscal_next . '-03-31 23:59:59');
        } elseif ($blog_frame->scope == BlogFrameScope::created_id) {
            // 自身の投稿のみ
            $users_id = null;
            if (Auth::check()) {
                $users_id = Auth::user()->id;
            }

            $query->where('blogs_posts.created_id', $users_id);
        }

        return $query;
    }
}
