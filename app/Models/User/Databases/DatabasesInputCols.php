<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class DatabasesInputCols extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['databases_inputs_id', 'databases_columns_id', 'value'];

    // リレーション
    /**
     * データベース入力
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @phpcsSuppress
     */
    public function databases_input()
    {
        return $this->belongsTo(DatabasesInputs::class, 'databases_inputs_id');
    }

}
