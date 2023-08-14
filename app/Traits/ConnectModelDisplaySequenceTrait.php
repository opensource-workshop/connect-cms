<?php
namespace App\Traits;

/**
 * display_sequenceの共通処理
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category model
 * @package CommonTrait
 */
trait ConnectModelDisplaySequenceTrait
{
    /**
     * 登録する表示順を取得
     */
    public static function getSaveDisplaySequence($query, $display_sequence, $id): int
    {
        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        if (!is_null($display_sequence)) {
            $display_sequence = intval($display_sequence);
        } else {
            $max_display_sequence = $query->where('id', '<>', $id)->max('display_sequence');
            $display_sequence = empty($max_display_sequence) ? 1 : $max_display_sequence + 1;
        }
        return $display_sequence;
    }
}
