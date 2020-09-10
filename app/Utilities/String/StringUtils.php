<?php

namespace App\Utilities\String;

use Illuminate\Support\Facades\Log;

class StringUtils
{
    /**
     * 空文字をnullに変換
     * Laravel公式のリクエストを自動トリムする処理と同じ処理
     * copy by Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::transform()
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function convertEmptyStringsToNull($value)
    {
        return is_string($value) && $value === '' ? null : $value;
    }

    /**
     * （再帰関数）入力値の前後をトリムする
     * (string)$valueの場合: UTF-8でトリムします。
     * (array)$valueの場合: arrayの中身を再回帰でトリムします。
     *
     * @param $value
     * @return array|string
     */
    public static function trimInput($value)
    {
        if (is_array($value)) {
            // 渡されたパラメータが配列の場合（radioやcheckbox等）の場合を想定
            $value = array_map(['self', 'trimInput'], $value);
        } elseif (is_string($value)) {
            // /u = UTF-8 として処理
            $value = preg_replace('/(^\s+)|(\s+$)/u', '', $value);
        }

        return $value;
    }

    /**
     * カンマ区切りの文字列を、一度配列にして、trim後、また文字列に戻す
     */
    public static function trimInputKanma(string $kanma_value): string
    {
        // 一度配列にして、trim後、また文字列に戻す。
        $tmp_array = explode(',', $kanma_value);
        // 配列値の入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
        $tmp_array = self::trimInput($tmp_array);
        $kanma_value2 = implode(', ', $tmp_array);
        return $kanma_value2;
    }
}
