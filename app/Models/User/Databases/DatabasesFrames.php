<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use App\UserableNohistory;

class DatabasesFrames extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'databases_id',
        'frames_id',
        'use_search_flag',
        'placeholder_search',
        'use_select_flag',
        'use_sort_flag',
        'default_sort_flag',
        'view_count',
        'default_hide',
        'use_filter_flag',
        'filter_search_keyword',
        'filter_search_columns',
        'view_page_id',
        'view_frame_id',
        'created_at',
        'updated_at'
    ];

    /**
     *  並べ替えが設定されているか判断
     */
    public function isUseSortFlag($flag = null)
    {

        // データベース上はカンマ区切りで入っている(nullをexplodeすると配列が一つ返ってくるのでチェック)
        if ($this->use_sort_flag) {
            $use_sort_flags = explode(',', $this->use_sort_flag);
        } else {
            $use_sort_flags = null;
        }

        // 空の場合
        if (empty($use_sort_flags)) {
            return false;
        }

        // 項目指定なしなら、並べ替え設定されていると判断
        if ($flag == null) {
            return true;
        }

        // 配列にマッチ
        if (in_array($flag, $use_sort_flags)) {
            return true;
        }
        return false;
    }

    /**
     * 各カラム以外の基本設定としての並べ替えが指定されているか判定
     */
    public function isBasicUseSortFlag()
    {

        // データベース上はカンマ区切りで入っている(nullをexplodeすると配列が一つ返ってくるのでチェック)
        if ($this->use_sort_flag) {
            $use_sort_flags = explode(',', $this->use_sort_flag);
        } else {
            $use_sort_flags = null;
        }

        // 空の場合
        if (empty($use_sort_flags)) {
            return false;
        }

        // 並び順 のkey配列を返す（$this->use_sort_flag（DatabasesFrames->use_sort_flag = 表示設定）のため、column'各カラム設定' を含む）
        // $enums_sort_flags_member_keys = \DatabaseSortFlag::getSortFlagsKeys();
        $enums_sort_flags_member_keys = \DatabaseSortFlag::getMemberKeys();

        // 配列にマッチ => NG. in_array()の配列どうしの比較で、配列の値を全て含んでいるかチェックには対応してない
        // if (in_array(array('created_asc', 'created_desc', 'updated_asc', 'updated_desc', 'random'), $use_sort_flags)) {
        // if (in_array($enums_sort_flags_keys, $use_sort_flags)) {
        //     return true;
        // }
        // return false;
        //
        // bugfix: 指定されたソート順が、使えるソート順以外に使われてないかチェック
        foreach ($use_sort_flags as $use_sort_flag) {
            if (in_array($use_sort_flag, $enums_sort_flags_member_keys)) {
                // 含まれているソート順はなにもしない
            } else {
                // 含まれていないソート順はエラーとしてfalse
                return false;
            }
        }
        // var_dump($enums_sort_flags_member_keys);
        // var_dump($use_sort_flags);
        return true;
    }

    /**
     *  各カラム以外の基本設定としての並べ替えが指定されているか判定
     */
    public function getBasicUseSortFlag()
    {

        // データベース上はカンマ区切りで入っている(nullをexplodeすると配列が一つ返ってくるのでチェック)
        if ($this->use_sort_flag) {
            $use_sort_flags = explode(',', $this->use_sort_flag);
        } else {
            $use_sort_flags = null;
        }

        // 空の場合
        if (empty($use_sort_flags)) {
            return array();
        }

        // 各カラム（column）設定は除外
        if (($key = array_search('column', $use_sort_flags)) !== false) {
            unset($use_sort_flags[$key]);
        }

        return $use_sort_flags;
    }
}
