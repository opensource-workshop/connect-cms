<?php

namespace App\Models\Common;

use App\Models\Core\Configs;
use App\UserableNohistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    /**
     * 設定ONなら配信停止した人を除くユーザーを取得する
     */
    public static function getUsersExcludingUnsubscribers(Collection $users, string $plugin_name) : Collection
    {
        // メール配信管理の使用
        if (Configs::getSharedConfigsValue('use_unsubscribe', '0') == '1') {

            // 配信停止ユーザー取得
            $unsubscriber‗users_ids = self::whereIn('users_id', $users->pluck('id'))
                ->where('plugin_name', $plugin_name)
                ->where('unsubscribed_flag', 1)
                ->pluck('users_id')
                ->toArray();

            // 配信停止ユーザーを除外
            $users = $users->reject(function ($user, $key) use ($unsubscriber‗users_ids) {
                return in_array($user->id, $unsubscriber‗users_ids);
            });
        }

        return $users;
    }

    /**
     * 配信停止ユーザーか
     */
    public static function isUnsubscriber(?int $users_id, string $plugin_name) : bool
    {
        $unsubscriber = self::firstOrNew([
            'users_id' => $users_id,
            'plugin_name' => $plugin_name,
        ]);

        return $unsubscriber->unsubscribed_flag == '1';
    }
}
