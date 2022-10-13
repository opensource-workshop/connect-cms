<?php

namespace App\Utilities\Migration;

use Illuminate\Support\Arr;

/**
 * 移行関連Utils
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 移行
 * @package Util
 */
class MigrationUtils
{
    /**
     * 配列の値の取得
     */
    public static function getArrayValue($array, $key1, $key2 = null, $default = "")
    {
        if (is_null($key2)) {
            return Arr::get($array, $key1, $default);
        }
        return Arr::get($array, "$key1.$key2", $default);
    }
}
