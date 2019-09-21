<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * モデルの保存時に自動的にユーザーID やユーザー名を保持するためのtrait
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
    }
}
