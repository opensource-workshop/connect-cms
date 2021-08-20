<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\UserableNohistory;

class Like extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 定数メンバ
    const like_button_default = 'いいね';

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'target',
        'target_id',
        'target_contents_id',
        'count',
    ];

    /**
     * いいねに対するleftJoin追加
     */
    public static function appendLikeLeftJoin($query, string $plugin_name, string $target_contents_id_column, string $target_id_column)
    {
        // $target_contents_id_column = 'blogs_posts.contents_id';
        // $target_id_column = 'blogs_posts.blogs_id';

        $query->leftJoin('likes', function ($join) use ($plugin_name, $target_contents_id_column, $target_id_column) {
            $join->on('likes.target_contents_id', '=', $target_contents_id_column)
                ->where('likes.target', $plugin_name)
                ->whereColumn('likes.target_id', $target_id_column)
                ->whereNull('likes.deleted_at');
            })
            ->leftJoin('like_users', function ($join) use ($plugin_name) {
                if (Auth::check()) {
                    // ログイン済み
                    $join->on('like_users.likes_id', '=', 'likes.id')
                        ->where('like_users.target', $plugin_name)
                        ->where('like_users.users_id', Auth::id())   // 自分のユーザIDで絞り込み
                        ->whereNull('like_users.deleted_at');
                } else {
                    // 未ログイン
                    $join->on('like_users.likes_id', '=', 'likes.id')
                        ->where('like_users.target', $plugin_name)
                        ->where('like_users.session_id', Session::getId())   // セッションID & users_id = null で絞り込み
                        ->whereNull('like_users.users_id')
                        ->whereNull('like_users.deleted_at');
                }
            });

        return $query;
    }

    /**
     * いいねの保存
     */
    public static function saveLike(string $plugin_name, int $target_id, int $target_contents_id) :int
    {
        $like = Like::firstOrNew([
            'target' => $plugin_name,
            'target_id' => $target_id,
            'target_contents_id' => $target_contents_id,
        ], [
            'count' => 0,
        ]);

        if (Auth::check()) {
            // ログイン済み
            // users_id で取得
            $like_user = LikeUser::firstOrNew([
                'target' => $plugin_name,
                'target_id' => $target_id,
                'target_contents_id' => $target_contents_id,
                'likes_id' => $like->id,
                'users_id' => Auth::id(),
            ]);

        } else {
            // 未ログイン
            // セッションID & users_id = null で取得
            $like_user = LikeUser::firstOrNew([
                'target' => $plugin_name,
                'target_id' => $target_id,
                'target_contents_id' => $target_contents_id,
                'likes_id' => $like->id,
                'session_id' => Session::getId(),
                'users_id' => null,
            ]);
        }

        // セッションを利用
        // セッション保持期間はデフォルト2時間（config/session.phpの'lifetime'参照）
        $like_histories = session('like_histories', '');
        $like_histories_array = explode(':', $like_histories);

        if (empty($like_user->id) || !in_array($like->id, $like_histories_array)) {
            // カウントアップ
            $like->count++;
            $like->save();

            // likes_idは $likeが１件目の時はここでしか取得できないため、再セットする
            $like_user->likes_id = $like->id;
            $like_user->session_id = Session::getId();
            $like_user->save();

            // session = いいねid & 区切り文字
            $like_histories = $like_histories . ':' . $like->id;
            // 先頭の':'を削除
            $like_histories = ltrim($like_histories, ':');

            // カウントしたいいねIDを記録
            session(['like_histories' => $like_histories]);
        }

        // return 100;
        return $like->count;
    }

    /**
     * いいね済みか
     */
    public static function isLiked(?int $like_id, ?int $like_users_id) :bool
    {
        $like_histories = session('like_histories', '');
        $like_histories_array = explode(':', $like_histories);
        // dd($like_users_id, $like_histories, $like_id, Session::getId());

        if (!empty($like_users_id)) {
            // 自分のユーザIDでlike_users検索済み. それでlike_users.idあり = like_usersにデータあり = いいね済み
            return true;
        } elseif (in_array($like_id, $like_histories_array)) {
            // いいね済み
            return true;
        } else {
            // 未いいね
            return false;
        }
   }
}
