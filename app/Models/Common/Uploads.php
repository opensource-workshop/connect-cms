<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Uploads extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['client_original_name', 'mimetype', 'extension', 'size', 'plugin_name', 'page_id', 'temporary_flag', 'check_method', 'created_id'];

    /**
     *  サイズのフォーマット
     */
    public function getFormatSize($r = 0)
    {
        $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        for ($i = 0; $this->size >= 1024 && $i < 4; $i++) $this->size /= 1024;
        return round($this->size, $r).$units[$i];
    }

    /**
     *  サイズのフォーマット
     */
    public function getFilename()
    {
        return $path_parts = pathinfo($this->client_original_name, PATHINFO_FILENAME );
    }

    /**
     *  プラグイン名
     */
    public function getPluginNameFull()
    {
        // プラグインテーブルをJoin で保持している場合にプラグイン名を使用
        if (!empty($this->plugin_name_full)) {
            return $this->plugin_name_full;
        }
        return $this->plugin_name;
    }

    /**
     *  ページ名
     */
    public function getPageName()
    {
        // ページテーブルをJoin で保持している場合にプラグイン名を使用
        if (!empty($this->page_name)) {
            return $this->page_name;
        }
        return $this->page_name;
    }

    /**
     *  ページリンク
     */
    public function getPageLinkTag($target = null)
    {
        // ページテーブルをJoin で保持している場合にページへのリンクタグを生成
        if (empty($this->permanent_link)) {
            return '';
        }

        $link = '<a href="' . url('/') . $this->permanent_link . '"';
        if (!empty($target)) {
            $link .= ' target="' . $target . '"';
        }
        $link .= '>' . $this->getPageName() . '</a>';

        return $link;
    }

    /**
     *  一時保存表記
     */
    public function getTemporaryFlagStr()
    {
        if ($this->temporary_flag == 1) {
            return '一時保存ファイル';
        }
        return '';
    }
}
