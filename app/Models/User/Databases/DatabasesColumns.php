<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;
use App\Enums\DatabaseColumnType;

class DatabasesColumns extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'databases_id',
        'column_type',
        'column_name',
        'required',
        'frame_col',
        'list_hide_flag',
        'detail_hide_flag',
        'label_hide_flag',
        'sort_flag',
        'search_flag',
        'select_flag',
        'title_flag',
        'display_sequence',
        'row_group',
        'column_group'
    ];

    /**
     * DBカラムの権限を取得
     * メソッドの呼び出しは`$databasesColumns->databasesColumnsRoles` で()を付けない
     */
    public function databasesColumnsRoles()
    {
        // 1対多
        return $this->hasMany('App\Models\User\Databases\DatabasesColumnsRole', 'databases_columns_id', 'id');
    }

    /**
     * 入力しないカラム型か
     */
    public function isNotInputColumnType()
    {
        // 登録日型・更新日型・公開日型・表示順型は入力しない
        if ($this->column_type == DatabaseColumnType::created ||
                $this->column_type == DatabaseColumnType::updated ||
                $this->column_type == DatabaseColumnType::posted ||
                $this->column_type == DatabaseColumnType::display) {
            return true;
        }
        return false;
    }

    /**
     * ファイルタイプのカラム型か
     */
    public static function isFileColumnType($column_type)
    {
        // ファイルタイプ
        if ($column_type == DatabaseColumnType::file ||
                $column_type == DatabaseColumnType::image ||
                $column_type == DatabaseColumnType::video) {
            return true;
        }
        return false;
    }

    /**
     * 埋め込みタグから除外するカラム型か
     */
    public static function isNotEmbeddedTagsColumnType($column_type)
    {
        // 登録日型・更新日型・公開日型・表示順型は入力しない
        if ($column_type == DatabaseColumnType::created ||
                $column_type == DatabaseColumnType::updated ||
                $column_type == DatabaseColumnType::posted ||
                $column_type == DatabaseColumnType::display) {
            return true;
        }
        return false;
    }
}
