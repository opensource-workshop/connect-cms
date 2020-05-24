<?php

namespace App\Models\User\Menus;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['frame_id', 'select_flag', 'page_ids', 'folder_close_font', 'folder_open_font', 'indent_font'];

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

    /**
     *  閉じるフォントの取得
     */
    public function getFolderCloseFont()
    {
        if ($this->folder_close_font == 1) {
            return '<span class="px-2"></span>';
        }
        else {
            return '<i class="fas fa-minus"></i>';
        }
    }

    /**
     *  開くフォントの取得
     */
    public function getFolderOpenFont()
    {
        if ($this->folder_open_font == 1) {
            return '<span class="px-2"></span>';
        }
        else {
            return '<i class="fas fa-plus"></i>';
        }
    }

    /**
     *  インデントフォントの取得
     */
    public function getIndentFont()
    {
        if ($this->indent_font == 1) {
            return '<span class="px-2"></span>';
        }
        elseif ($this->indent_font == 2) {
            return '<i class="fas fa-minus"></i>';
        }
        else {
            return '<i class="fas fa-chevron-right"></i>';
        }
    }
}
