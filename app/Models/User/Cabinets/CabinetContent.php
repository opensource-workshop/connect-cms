<?php

namespace App\Models\User\Cabinets;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;
use App\Models\Common\Uploads;
use Kalnoy\Nestedset\NodeTrait;

class CabinetContent extends Model
{
    const is_folder_on = 1;
    const is_folder_off = 0;

    use NodeTrait;
    // 保存時のユーザー関連データの保持
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['cabinet_id', 'upload_id', 'name', 'is_folder'];

    // NC2移行用の一時項目
    public $migrate_parent_id = 0;
    /**
     * キャビネットコンテントに紐づくアップロードを取得
     */
    public function upload()
    {
        // uploadsテーブルをこのレコードから見て 1:1 で紐づけ
        // キーは指定しておく。Uploads の id にこのレコードの upload_id を紐づける。
        // withDefault() を指定しておくことで、Uploads がないときに空のオブジェクトが返ってくるので、null po 防止。
        return $this->hasOne(Uploads::class, 'id', 'upload_id')->withDefault();
    }

    /**
     * 画面表示用のファイル名を取得する
     *
     * @return string  画面表示用のファイル名
     */
    public function getDisplayNameAttribute()
    {
        //キャビネットプラグインでフォルダ名・ファイル名が変更可能なため、uploadsではなくcabinet_contentsのnameを使用する
        return $this->name;
    }
}
