<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Nc3Category extends Model
{
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
        return Nc3Category::select('categories.*', 'categories_languages.name')
            ->join('categories_languages', function ($join) {
                $join->on('categories_languages.category_id', '=', 'categories.id')
                    ->where('categories_languages.language_id', Nc3Language::language_id_ja);
            })
            ->whereIn('categories.block_id', $block_ids)
            ->get();
    }
}
