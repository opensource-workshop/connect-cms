<?php

namespace App\Models\User\Photoalbums;

use Illuminate\Database\Eloquent\Model;

// 表紙フラグの更新で複数レコードを更新する処理があるが、その際は更新日時を変更したくないため、コメント。
//use App\UserableNohistory;
use App\Models\Common\Uploads;
use Kalnoy\Nestedset\NodeTrait;

class PhotoalbumContent extends Model
{
    const is_folder_on = 1;
    const is_folder_off = 0;
    const is_cover_on = 1;
    const is_cover_off = 0;
    const UPDATED_AT = null; // 更新日を自動で設定しないための処置

    use NodeTrait;

    // 保存時のユーザー関連データの保持
    //use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['photoalbum_id', 'upload_id', 'poster_upload_id', 'name', 'width', 'height', 'description', 'is_folder', 'is_cover', 'mimetype'];

    // NC2移行用の一時項目
    public $migrate_parent_id = 0;

    /**
     * フォトアルバムコンテントに紐づくアップロードを取得
     */
    public function upload()
    {
        // uploadsテーブルをこのレコードから見て 1:1 で紐づけ
        // キーは指定しておく。Uploads の id にこのレコードの upload_id を紐づける。
        // withDefault() を指定しておくことで、Uploads がないときに空のオブジェクトが返ってくるので、null po 防止。
        return $this->hasOne(Uploads::class, 'id', 'upload_id')->withDefault();
    }

    /**
     * フォトアルバムコンテントに紐づくポスターアップロードを取得
     */
    public function posterUpload()
    {
        // uploadsテーブルをこのレコードから見て 1:1 で紐づけ
        // キーは指定しておく。Uploads の id にこのレコードの upload_id を紐づける。
        // withDefault() を指定しておくことで、Uploads がないときに空のオブジェクトが返ってくるので、null po 防止。
        return $this->hasOne(Uploads::class, 'id', 'poster_upload_id')->withDefault();
    }

    public function isImage($mimetype)
    {
        return Uploads::isImage($mimetype);
    }

    public function isVideo($mimetype)
    {
        return Uploads::isVideo($mimetype);
    }

    /**
     * 画面表示用のファイル名を取得する
     *
     * @return string  画面表示用のファイル名
     */
    public function getDisplayNameAttribute()
    {
        $displayName = $this->name;
        // 管理機能のアップロードファイル管理で、ファイル名の変更ができるため、
        // ファイルはアップロードテーブルから名称取得する
        if ($this->is_folder === self::is_folder_off) {
            $displayName = $this->upload->client_original_name;
        }
        return $displayName;
    }

    /**
     * 更新日、登録日の大きい方を返す。
     *
     * @return string  更新日、登録日の大きい方（Y-m-d H:i:s）
     */
    public function getUpdateOrCreatedAt($format = null)
    {
        $return_date = $this->created_at;
        if ($this->updated_at > $this->created_at) {
            $return_date = $this->updated_at;
        }
        if (empty($return_date)) {
            return '';
        }
        if (empty($format)) {
            return $return_date;
        }
        return date($format, strtotime($return_date));
    }

    /**
     * 拡大表示用のサイズを取得する
     * アルバムの拡大写真は、サムネイルをクリックした際に動的に表示する。
     * その際、写真表示用フレームの大きさが指定されていないと、写真を読み込んだ後、フレームの大きさが変化することで、ちらつきを感じる。
     * ここでフレームの大きさを取得し、写真表示用フレームの大きさに設定しておくことで、このちらつきをなくす。
     *
     * @return string  拡大表示用のサイズ文字列
     */
    public function getModalMinSize()
    {
        // もし、幅、高さに有効な数値が入っていなかった場合は、空を返す。
        if (!is_int($this->width) || !is_int($this->height)) {
            return "";
        }

        // 幅が800px を超えていた場合、表示幅は800px に縮小される。そのため、高さも表示幅の縮小率と同じ高さで計算する。
        if ($this->width > 800) {
            $height = ceil(800 / $this->width * $this->height);
        } else {
            $height = $this->height;
        }

        // 高さが800px より大きいときは、800+166=966px
        // 高さが800px より小さいときは、表示の高さ+166
        if ($height > 800) {
            return "min-width: 800px; min-height: 966px;";
        } else {
            return "min-width: 800px; min-height: " . $height . "px;";
        }
    }

    /**
     * カバー写真のIDを返す。
     * 画像レコードなら画像のID、動画レコードなら、ポスター画像のID
     *
     * @return int アップロードID
     */
    public function getCoverFileId()
    {
        if (Uploads::isImage($this->mimetype)) {
            return $this->upload_id;
        } else {
            return $this->poster_upload_id;
        }
    }
}
