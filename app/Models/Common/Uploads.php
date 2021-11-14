<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Uploads extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['client_original_name', 'mimetype', 'extension', 'size', 'width', 'height', 'plugin_name', 'page_id', 'temporary_flag', 'check_method', 'created_id'];

    /**
     *  サイズのフォーマット
     */
    public function getFormatSize($r = 0)
    {
        $size = $this->size;
        $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, $r).$units[$i];
    }

    /**
     *  サイズのフォーマット
     */
    public function getFilename()
    {
        // 環境によってはsetlocale しておかないと、ファイル名がうまくpathinfo で取得できなかった。
        // 2020-12-15 Connect-CMS 公式サイトで、ファイル名が空になったり一部しか取得できないケースがあった。
        // false を返すことなどもあるようで、ワーニングの抑止の意味も含めて @ 付きでCall
        @setlocale(LC_ALL, 'ja_JP.UTF-8');
        return $path_parts = pathinfo($this->client_original_name, PATHINFO_FILENAME);
    }

    /**
     * IDからファイル名(拡張子なし)取得
     */
    public static function getFilenameNoExtensionById($id)
    {
        if (empty($id)) {
            return '';
        }

        $uploads = Uploads::find(intval($id));
        if (empty($uploads)) {
            return '';
        }

        // 末尾の拡張子を除いたファイル名を取得
        $newFileName = rtrim($uploads->client_original_name, '.'.$uploads->extension);
        return $newFileName;
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
