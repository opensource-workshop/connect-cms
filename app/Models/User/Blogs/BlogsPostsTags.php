<?php

namespace App\Models\User\Blogs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class BlogsPostsTags extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['posted_at'];

    /**
     * タグデータをポストデータに紐づけ
     */
    public static function stringTags($blogs_posts)
    {
        // タグ：画面表示するデータのblogs_posts_id を集める
        $posts_ids = $blogs_posts->pluck('id');

        // タグ：タグデータ取得
        $blogs_posts_tags_row = self::whereIn('blogs_posts_id', $posts_ids)->get();

        // タグ：タグデータ詰めなおし（ブログデータの一覧にあてるための外配列）
        $blogs_posts_tags = array();
        foreach ($blogs_posts_tags_row as $record) {
            $blogs_posts_tags[$record->blogs_posts_id][] = $record->tags;
        }

        // タグ：タグデータをポストデータに紐づけ
        foreach ($blogs_posts as &$blogs_post) {
            if (array_key_exists($blogs_post->id, $blogs_posts_tags)) {
                $blogs_post->tags = $blogs_posts_tags[$blogs_post->id];
            }
        }

        return $blogs_posts;
    }
}
