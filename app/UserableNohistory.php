<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * 履歴なしUserable
 * モデルの保存時に自動的にユーザーID やユーザー名を保持するためのtrait
 * コード管理などの履歴なしmodelに適用するtraitため、created_idを自動登録する
 * 
 * 使用するには、モデルでcreated_id、created_name、updated_id、updated_nameを定義してuseする。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Core
 * @package App
 */
trait UserableNohistory
{
    public static function bootUserableNohistory()
    {
        /**
         *  オブジェクトcreate 時のイベントハンドラ
         */
        static::creating(function (Model $model) {
            $model->created_id   = Auth::user()->id;
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
