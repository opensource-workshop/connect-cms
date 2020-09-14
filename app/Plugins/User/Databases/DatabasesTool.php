<?php

namespace App\Plugins\User\Databases;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Traits\ConnectCommonTrait;

/**
 * データベースの便利関数
 */
class DatabasesTool
{
    use ConnectCommonTrait;

    /**
     * 権限のよって非表示columのdatabases_columns_id配列を取得する
     * $display_flag_column_name = regist_edit_display_flag|list_detail_display_flag
     */
    public function getHideColumnsIds($databases_columns, $display_flag_column_name = 'list_detail_display_flag')
    {
        if (empty($databases_columns)) {
            return [];
        }

        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
        // Log::debug('role_article_admin: '.var_export($this->isCan('role_article_admin'), true));
        // Log::debug('role_arrangement: '.var_export($this->isCan('role_arrangement'), true));
        // Log::debug('role_article: '.var_export($this->isCan('role_article'), true));
        // Log::debug('role_approval: '.var_export($this->isCan('role_approval'), true));
        // Log::debug('role_reporter: '.var_export($this->isCan('role_reporter'), true));

        $databases_hide_columns_ids = [];

        foreach ($databases_columns as $databases_column) {
            if ($this->isCan('role_article_admin')) {
                // コンテンツ管理者のユーザは、必ず当カラムを表示します。
                continue;
            }

            // 権限で表示カラムを制御
            if (!$databases_column->role_display_control_flag) {
                // 制御しない表示カラムはスルー
                continue;
            }

            // 権限で表示カラムを制御する場合、一度に非表示扱いにする
            // 該当権限で表示フラグをゲットできたら、array keyを指定して非表示扱いから取り除く
            $databases_hide_columns_ids[$databases_column->id] = $databases_column->id;
            // var_dump($databases_column->id);

            // カラムの表示権限データ取得
            $databases_columns_roles = $databases_column->databasesColumnsRoles;

            if (Auth::user()) {
                // ログイン済み
                foreach ($databases_columns_roles as $databases_columns_role) {
                    if ($this->isCan('role_article') &&
                            $databases_columns_role->role_name == \DatabaseColumnRoleName::role_article &&
                            $databases_columns_role->$display_flag_column_name == 1) {
                        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
                        // Log::debug(var_export('モデレータ', true));

                        // モデレータ権限あり & モデレータ表示のcolumn
                        // 非表示扱いから取り除く(=表示する)
                        unset($databases_hide_columns_ids[$databases_columns_role->databases_columns_id]);
                        continue 2;
                    } elseif ($this->isCan('role_reporter') &&
                            $databases_columns_role->role_name == \DatabaseColumnRoleName::role_reporter &&
                            $databases_columns_role->$display_flag_column_name == 1) {
                        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
                        // Log::debug(var_export('編集者権限', true));

                        // 編集者権限あり & 編集者表示のcolumn
                        // 非表示扱いから取り除く(=表示する)
                        unset($databases_hide_columns_ids[$databases_columns_role->databases_columns_id]);
                        continue 2;
                    } elseif (!$this->isCan('role_arrangement') &&
                            !$this->isCan('role_article') &&
                            !$this->isCan('role_approval') &&
                            !$this->isCan('role_reporter') &&
                            $databases_columns_role->role_name == \DatabaseColumnRoleName::no_role &&
                            $databases_columns_role->$display_flag_column_name == 1) {
                        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
                        // Log::debug(var_export('権限なし', true));

                        // 権限なし(プラグイン管理者・モデレータ・承認者・編集者のいずれの権限も付いていない)
                        // & 権限なし表示のcolumn
                        // 非表示扱いから取り除く(=表示する)
                        unset($databases_hide_columns_ids[$databases_columns_role->databases_columns_id]);
                        continue 2;
                    }
                }
            } else {
                // 未ログイン
                foreach ($databases_columns_roles as $databases_columns_role) {
                    // 未ログインで非表示のcolumnは、取り除く
                    if ($databases_columns_role->role_name == \DatabaseColumnRoleName::not_login &&
                            $databases_columns_role->$display_flag_column_name == 1) {
                        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
                        // Log::debug(var_export('未ログイン', true));

                        // 非表示扱いから取り除く(=表示する)
                        unset($databases_hide_columns_ids[$databases_columns_role->databases_columns_id]);
                        continue 2;
                    }
                }
            }
        }
        return $databases_hide_columns_ids;
    }

    /**
     * 検索キーワードの絞り込み
     *
     * データベースプラグイン例）   $where_in_colum_name = 'databases_inputs.id'
     * データベース検索プラグイン例）$where_in_colum_name = 'databases_inputs_id'
     */
    public static function appendSearchKeyword($where_in_colum_name, $inputs_query, $databases_columns_ids, $hide_columns_ids, $search_keyword)
    {
        $inputs_query->whereIn($where_in_colum_name, function ($query) use ($search_keyword, $databases_columns_ids, $hide_columns_ids) {
                        // 縦持ちのvalue を検索して、行の id を取得。search_flag で対象のカラムを絞る。
                        $query->select('databases_inputs_id')
                                ->from('databases_input_cols')
                                ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                ->where('databases_columns.search_flag', 1)
                                ->whereIn('databases_columns.id', $databases_columns_ids)
                                ->whereNotIn('databases_columns.id', $hide_columns_ids)
                                ->where('value', 'like', '%' . $search_keyword . '%')
                                ->groupBy('databases_inputs_id');
        });
        return $inputs_query;
    }

    /**
     * カラムの絞り込み
     *
     * データベースプラグイン例）   $where_in_colum_name = 'databases_inputs.id'
     * データベース検索プラグイン例）$where_in_colum_name = 'databases_inputs_id'
     */
    public static function appendSearchColumns($where_in_colum_name, $inputs_query, $search_columns)
    {
        foreach ($search_columns as $search_column) {
            if ($search_column && $search_column['columns_id'] && $search_column['value']) {
                $inputs_query->whereIn($where_in_colum_name, function ($query) use ($search_column) {
                        // 縦持ちのvalue を検索して、行の id を取得。column_id で対象のカラムを絞る。
                        $query->select('databases_inputs_id')
                                ->from('databases_input_cols')
                                ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                ->where('databases_columns_id', $search_column['columns_id']);

                    if ($search_column['where'] == 'PART') {
                        $query->where('value', 'LIKE', '%' . $search_column['value'] . '%');
                    } else {
                        $query->where('value', $search_column['value']);
                    }
                    $query->groupBy('databases_inputs_id');
                });
            }
        }

        return $inputs_query;
    }
}
