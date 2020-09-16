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
    public static function trimInputKanma($kanma_value): string
    {
        // 一度配列にして、trim後、また文字列に戻す。
        $tmp_array = explode(',', $kanma_value);
        // 配列値の入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
        $tmp_array = self::trimInput($tmp_array);
        // bugfix: カンマの前後にspace入れない
        $kanma_value2 = implode(',', $tmp_array);
        return $kanma_value2;
    }

    /**
     * 数値項目に使う事を想定。
     * 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）＆ 全角→半角へ丸める
     * 郵便場合（111-2222）、電話番号（111-2222-3333）の入力は対応してない。
     */
    public static function convertNumericAndMinusZenkakuToHankaku($columns_value)
    {
        if ($columns_value) {
            // 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）
            // －１, －１等のマイナス１の入力を丸める。
            $replace_defs = [
                'ー' => '-',
                '－' => '-',
                '―' => '-'
            ];
            $search = array_keys($replace_defs);
            $replace = array_values($replace_defs);

            // 全角→半角へ丸めて、一時変数に保持
            $tmp_numeric_columns_value = mb_convert_kana(
                str_replace(
                    $search,
                    $replace,
                    $columns_value
                ),
                'n'
            );

            if (is_numeric($tmp_numeric_columns_value)) {
                // 全角→半角変換した結果が数値の場合
                return $tmp_numeric_columns_value;
            }
        }

        return $columns_value;
    }
}
