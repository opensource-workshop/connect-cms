<?php

namespace App\Models\User\Bbses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

/**
 * 掲示板・フレーム
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板・プラグイン
 * @package モデル
 */
class BbsFrame extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['bbs_id', 'frame_id', 'view_format', 'thread_sort_flag', 'list_format', 'thread_format', 'list_underline', 'thread_caption', 'view_count'];

    /**
     * 表示件数
     */
    public function getViewCount()
    {
        // 初期値は 10
        if ($this->view_count === null) {
            return 10;
        }
        return $this->view_count;
    }
}
