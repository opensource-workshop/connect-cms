<?php

namespace App\Models\User\Blogs;

use App\Models\Common\Frame;
use App\UserableNohistory;
use Illuminate\Database\Eloquent\Model;

class Blogs extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'bucket_id',
        'blog_name',
        'use_like',
        'use_view_count_spectator',
        'narrowing_down_type',
        'narrowing_down_type_for_created_id',
    ];

    /**
     * 紐づくブログID とフレームデータの取得
     */
    public static function getBlogFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::
            select(
                'frames.*',
                'blogs.id as blogs_id',
                'blogs.blog_name',
                'blogs.rss',
                'blogs.rss_count',
                'blogs.use_like',
                'blogs.like_button_name',
                'blogs.use_view_count_spectator',
                'blogs.narrowing_down_type',
                'blogs.narrowing_down_type_for_created_id',
                'blogs_frames.scope',
                'blogs_frames.scope_value',
                'blogs_frames.important_view',
            )
            ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
            ->leftJoin('blogs_frames', 'blogs_frames.frames_id', '=', 'frames.id')
            ->where('frames.id', $frame_id)
            ->first();
        return $frame;
    }
}
