<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DatabasesFrames extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'databases_id',
        'frames_id',
        'use_search_flag',
        'use_select_flag',
        'use_sort_flag',
        'default_sort_flag',
        'view_count',
        'default_hide',
        'view_page_id',
        'view_frame_id',
        'created_at',
        'updated_at'
    ];

    /**
     *  並べ替えが設定されているか判断
     */
    public function isUseSortFlag($flag = null) {

        // データベース上はカンマ区切りで入っている(nullをexplodeすると配列が一つ返ってくるのでチェック)
        if ($this->use_sort_flag) {
            $use_sort_flags = explode(',', $this->use_sort_flag);
        }
        else {
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
     *  各カラム以外の基本設定としての並べ替えが指定されているか判定
     */
    public function isBasicUseSortFlag() {

        // データベース上はカンマ区切りで入っている(nullをexplodeすると配列が一つ返ってくるのでチェック)
        if ($this->use_sort_flag) {
            $use_sort_flags = explode(',', $this->use_sort_flag);
        }
        else {
            $use_sort_flags = null;
        }

        // 空の場合
        if (empty($use_sort_flags)) {
            return false;
        }

        // 配列にマッチ
        if (in_array(array('created_asc', 'created_desc', 'updated_asc', 'updated_desc', 'random'), $use_sort_flags)) {
            return true;
        }
        return false;
    }

    /**
     *  各カラム以外の基本設定としての並べ替えが指定されているか判定
     */
    public function getBasicUseSortFlag() {

        // データベース上はカンマ区切りで入っている(nullをexplodeすると配列が一つ返ってくるのでチェック)
        if ($this->use_sort_flag) {
            $use_sort_flags = explode(',', $this->use_sort_flag);
        }
        else {
            $use_sort_flags = null;
        }

        // 空の場合
        if (empty($use_sort_flags)) {
            return array();
        }

        // 各カラム（column）設定は除外
        if(($key = array_search('column', $use_sort_flags)) !== false) {
            unset($use_sort_flags[$key]);
        }

        return $use_sort_flags;
    }
}
