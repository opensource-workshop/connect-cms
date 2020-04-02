<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * 履歴なしUserable
 * モデルの保存時に自動的にユーザーID やユーザー名を保持するためのtrait
 * コード管理などの履歴なしmodelに適用するtraitため、created_idを自動登録する
 * 
 * 使用するには、モデルでcreated_id、created_name、updated_id、updated_nameを定義してuseする。
 *
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
            // 未ログインなら処理しない。（未ログインで登録する処理、フォーム等に対応）
            if (! Auth::user()) {
                return;
            }

            $model->created_id   = Auth::user()->id;
            $model->created_name = Auth::user()->name;
        });

        /**
         *  オブジェクトupdate 時のイベントハンドラ
         */
        static::updating(function (Model $model) {
            // 未ログインなら処理しない
            if (! Auth::user()) {
                return;
            }

            $model->updated_id   = Auth::user()->id;
            $model->updated_name = Auth::user()->name;
        });

        /**
         *  オブジェクトdelete 時のイベントハンドラ
         */
        static::deleting(function (Model $model) {
            // 未ログインなら処理しない
            if (! Auth::user()) {
                return;
            }

            // カラムあるか
            if (Schema::hasColumn($model->getTable(), 'deleted_id') && Schema::hasColumn($model->getTable(), 'deleted_name')) {
                $model->deleted_id   = Auth::user()->id;
                $model->deleted_name = Auth::user()->name;
                // delete時はsave走らないため、値をセットしても保存されない。そのため明示的にsaveする。
                $model->save();
            }
        });
    }
}
