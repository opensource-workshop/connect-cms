<?php

namespace App\Models\Common;

use App\Models\Core\Configs;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Facades\Image;

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

    /**
     *  画像ファイルか判定
     */
    public static function isImage($mimetype)
    {
        if ($mimetype == 'image/png'  ||
            $mimetype == 'image/jpeg' ||
            $mimetype == 'image/gif') {
            return true;
        }
        return false;
    }

    /**
     *  動画ファイルか判定
     */
    public static function isVideo($mimetype)
    {
        if ($mimetype == 'video/mp4') {
            return true;
        }
        return false;
    }

    /**
     *  画像ファイルの縮小
     *
     *  @param \Illuminate\Http\UploadedFile $file アップロードファイル
     *  @param int $max_size 許容する最大サイズ（これより大きい幅、高さがあれば縮小する）
     *  @return \Intervention\Image\Image 画像データ
     */
    public static function shrinkImage($file, $max_size)
    {
        // GDのリサイズでメモリを多く使うため、memory_limitセット
        $configs = Configs::getSharedConfigs();
        $memory_limit_for_image_resize = Configs::getConfigsValue($configs, 'memory_limit_for_image_resize', '256M');
        ini_set('memory_limit', $memory_limit_for_image_resize);

        // 画像オブジェクトの生成
        $image = Image::make($file);

        // 画像の回転対応: orientate()
        $image = $image->orientate();

        // サイズを確認して、縮小の必要がなければそのまま返す。
        if ($image->width() <= $max_size && $image->height() <= $max_size) {
            return $image;
        }

        // 縮小
        return $image->resize(
            $max_size,
            $max_size,
            function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            }
        );
    }
}
