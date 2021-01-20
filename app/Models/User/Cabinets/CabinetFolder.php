<?php

namespace App\Models\User\Cabinets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

use Kalnoy\Nestedset\NodeTrait;

use App\Userable;

/**
 * キャビネット・フォルダ
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
 * @package モデル
 */
class CabinetFolder extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['cabinet_id', 'title', 'description', '_lft', '_rgt', 'parent_id'];

    // DB にないカラム
    protected $guarded = ['depth'];

    // 入れ子集合モデル
    use NodeTrait;
}
