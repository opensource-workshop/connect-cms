<?php

namespace App\Models\Common;

use App\UserableNohistory;
use Illuminate\Database\Eloquent\Model;

/**
 * メール配信解除のモデル
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メール配信管理
 * @package Model
 */
class Unsubscriber extends Model
{
    /** 保存時のユーザー関連データの保持 */
    use UserableNohistory;

    /** 更新する項目の定義 */
    protected $fillable = [
        'users_id',
        'plugin_name',
        'unsubscribed_flag',
    ];
}
