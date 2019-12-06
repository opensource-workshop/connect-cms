<?php

namespace App\Models\User\Menus;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['frame_id', 'select_flag', 'page_ids'];

    /**
     *  指定されたページID がメニューで表示onになっているか判定
     */
    public function onPage($page_id = null)
    {
        // 調査対象のページIDが空の場合はfalse
        if (empty($page_id)) {
            return false;
        }

        // カンマ区切りのページID を配列にして、指定されたページID があるか判定
        $page_ids = explode(',', $this->page_ids);
        if (in_array($page_id, $page_ids)) {
            return true;
        }
        return false;
    }
}
