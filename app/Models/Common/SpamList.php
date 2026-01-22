<?php

namespace App\Models\Common;

use App\UserableNohistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * スパムリストモデル
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スパム管理
 * @package Model
 */
class SpamList extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use SoftDeletes;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'target_plugin_name',
        'target_id',
        'block_type',
        'block_value',
        'memo',
    ];

    /**
     * フォームプラグイン用のスパムリストを取得
     *
     * @param int|null $forms_id フォームID（nullの場合は全体のみ）
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getFormsSpamLists($forms_id = null)
    {
        $query = self::where('target_plugin_name', 'forms');

        if ($forms_id) {
            $query->where(function ($q) use ($forms_id) {
                $q->where('target_id', $forms_id)
                  ->orWhereNull('target_id');
            });
        } else {
            $query->whereNull('target_id');
        }

        return $query->orderBy('block_type')
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    /**
     * 適用範囲の表示名を取得
     *
     * @return string
     */
    public function getScopeDisplayName()
    {
        if (is_null($this->target_id)) {
            return '全体';
        }
        return 'このフォーム';
    }

    /**
     * 全体適用かどうか
     *
     * @return bool
     */
    public function isGlobalScope()
    {
        return is_null($this->target_id);
    }

    /**
     * 重複チェック付きでスパムリストに追加
     *
     * @param string $target_plugin_name 対象プラグイン名
     * @param int|null $target_id 対象ID（nullの場合は全体適用）
     * @param string $block_type ブロック種別
     * @param string $block_value ブロック対象の値
     * @param string|null $memo メモ
     * @return bool 追加成功時true、重複時false
     */
    public static function addIfNotExists($target_plugin_name, $target_id, $block_type, $block_value, $memo = null)
    {
        $exists = self::where('target_plugin_name', $target_plugin_name)
            ->where('target_id', $target_id)
            ->where('block_type', $block_type)
            ->where('block_value', $block_value)
            ->exists();

        if ($exists) {
            return false;
        }

        self::create([
            'target_plugin_name' => $target_plugin_name,
            'target_id'          => $target_id,
            'block_type'         => $block_type,
            'block_value'        => $block_value,
            'memo'               => $memo,
        ]);
        return true;
    }
}
