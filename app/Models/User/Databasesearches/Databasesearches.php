<?php

namespace App\Models\User\Databasesearches;

use Illuminate\Database\Eloquent\Model;

class Databasesearches extends Model
{
    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'databasesearches_name', 'view_count', 'view_columns', 'condition', 'frame_select', 'target_frame_ids', 'created_at', 'updated_at'];

    /**
     *  指定したFrame が表示対象か判定
     *
     */
    public function isTargetFrame($frame_id)
    {
        if (in_array($frame_id, explode(',', $this->target_frame_ids))) {
            return true;
        }
        return false;
    }
}
