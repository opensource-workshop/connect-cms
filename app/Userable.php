<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * モデルの保存時に自動的にユーザーID やユーザー名を保持するためのtrait
 * 履歴ありmodelに適用（承認など）: created_idを自動登録しない
 * 履歴なしmodelに適用（コード管理など）: created_idを自動登録する
 * 
 * 使用するには、モデルでcreated_id、created_name、updated_id、updated_nameを定義してuseする。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Core
 * @package App
 */
trait Userable
{
    public static function bootUserable()
    {
        /**
         *  オブジェクトcreate 時のイベントハンドラ
         */
        static::creating(function (Model $model) {
            // created_idはデータ更新権限のチェックのため、最初に記事を書いたユーザのものを引き継ぐ必要があるので、自動登録はしない。
            // $model->created_id   = Auth::user()->id;
            // 履歴ありモデル（テーブル）には status があり値は必ずセットされるはず。なければ履歴なしモデルなので、created_idを自動登録する。
            if (is_null($model->status)){
                $model->created_id   = Auth::user()->id;
            }
            $model->created_name = Auth::user()->name;
        });

        /**
         *  オブジェクトupdate 時のイベントハンドラ
         */
        static::updating(function (Model $model) {
            $model->updated_id   = Auth::user()->id;
            $model->updated_name = Auth::user()->name;
        });

        /**
         *  オブジェクトdelete 時のイベントハンドラ
         */
        static::deleting(function (Model $model) {
            $model->deleted_id   = Auth::user()->id;
            $model->deleted_name = Auth::user()->name;
        });
    }
}
