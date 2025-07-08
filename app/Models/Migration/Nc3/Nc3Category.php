<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Nc3Category extends Model
{
    use HasFactory;

    /**
     * タイムスタンプの自動更新を無効にする
     */
    public $timestamps = false;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'categories';

    /**
     * block_idでカテゴリ 取得
     */
    public static function getCategoriesByBlockIds($block_ids): Collection
    {
        return Nc3Category::select('categories.*', 'categories_languages.name', 'category_orders.weight as display_sequence')
            ->join('categories_languages', function ($join) {
                $join->on('categories_languages.category_id', '=', 'categories.id')
                    ->where('categories_languages.language_id', Nc3Language::language_id_ja);
            })
            ->join('category_orders', function ($join) {
                $join->on('category_orders.category_key', '=', 'categories.key');
            })
            ->whereIn('categories.block_id', $block_ids)
            ->orderBy('category_orders.block_key')
            ->orderBy('category_orders.weight')
            ->get();
    }
}
