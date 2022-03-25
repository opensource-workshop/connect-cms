<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;
use App\Enums\ReservationColumnType;

class ReservationsColumn extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'columns_set_id',
        'column_type',
        'column_name',
        'required',
        'hide_flag',
        'title_flag',
        'display_sequence',
    ];

    /**
     * 入力しないカラム型か
     */
    public function isNotInputColumnType()
    {
        // 登録日型・更新日型等は入力しない
        if ($this->column_type == ReservationColumnType::created ||
                $this->column_type == ReservationColumnType::updated ||
                $this->column_type == ReservationColumnType::created_name ||
                $this->column_type == ReservationColumnType::updated_name) {
            return true;
        }
        return false;
    }

    /**
     * 埋め込みタグから除外するカラム型か
     */
    public function isNotEmbeddedTagsColumnType()
    {
        // 登録日型・更新日型等は入力しない
        if ($this->column_type == ReservationColumnType::created ||
                $this->column_type == ReservationColumnType::updated ||
                $this->column_type == ReservationColumnType::created_name ||
                $this->column_type == ReservationColumnType::updated_name ||
                // [TODO] 選択肢未対応
                $this->column_type == ReservationColumnType::radio) {
            return true;
        }
        return false;
    }
}
