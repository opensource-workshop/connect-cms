<?php

namespace App\Models\User\Tabs;

use Illuminate\Database\Eloquent\Model;

class Tabs extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['frame_id', 'default_frame_id', 'frame_ids'];

    /**
     *  指定されたフレームID がタブで表示onになっているか判定
     */
    public function onFrame($frame_id = null)
    {
        // 調査対象のフレームIDが空の場合はfalse
        if (empty($frame_id)) {
            return false;
        }

        // カンマ区切りのフレームID を配列にして、指定されたフレームID があるか判定
        $frame_ids = explode(',', $this->frame_ids);
        if (in_array($frame_id, $frame_ids)) {
            return true;
        }
        return false;
    }
}
