<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * スパムブロック履歴モデル
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スパム管理
 * @package Model
 */
class SpamBlockHistory extends Model
{
    use HasFactory;

    /**
     * updated_at は使用しない
     */
    const UPDATED_AT = null;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'spam_list_id',
        'forms_id',
        'block_type',
        'block_value',
        'client_ip',
        'submitted_email',
    ];

    /**
     * スパムリストとのリレーション
     */
    public function spamList()
    {
        return $this->belongsTo(SpamList::class, 'spam_list_id');
    }
}
