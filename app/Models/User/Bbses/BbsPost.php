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
    protected $fillable = ['bbs_id', 'title', 'body', 'root_id', 'thread_updated_at', 'temporary_flag', '_lft', '_rgt', 'parent_id'];

    // 入れ子集合モデル
    use NodeTrait;

    /**
     * Scope の設定。root_id 単位での入れ子集合モデルを設定することで、スレッド内での入れ子集合モデルとする。
     * スレッド内での入れ子集合モデルとすることで、不要なデータベース更新を減らしトラブルを軽減する。
     * 根記事の取得は parent_id = null で取得可能
     */
    protected function getScopeAttributes()
    {
        return ['root_id'];
    }
}
