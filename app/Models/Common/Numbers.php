<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

class Numbers extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['plugin_name', 'buckets_id', 'serial_number', 'prefix'];

    /**
     * 連番取得
     */
    public static function getNo($plugin_name = null, $buckets_id = null, $prefix = null): int
    {
        // 連番データは、払いだした最大の数値を保持している状態。

        // firstOrCreate で最初の連番に 0 を指定して、取得した値をインクリメント
        $numbers = self::firstOrCreate(
            [
                'plugin_name'   => $plugin_name,
                'buckets_id'    => $buckets_id,
                'prefix'        => $prefix
            ],
            [
                'serial_number' => 0,
            ]
        );

        // インクリメント
        $numbers->increment('serial_number', 1);
        // インクリメントでupdating()イベントが走らないため、saveを実行してupdated_id,updated_nameを自動セット
        $numbers->save();

        return $numbers->serial_number;
    }
}
