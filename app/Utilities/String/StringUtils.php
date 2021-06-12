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

    /**
     * 値から改行を取りにぞいたものを返す
     */
    public static function getNobrValue($value)
    {
        return str_replace("\r\n", "", $value);
    }

    // /**
    //  * 検索ワードのパース
    //  */
    // public static function parseSearchWords($search_words)
    // {
    //     // --- debug
    //     // $search_words = " apple,apple bear \"Tom, Cruise\" or 'Mickey Mouse' another  word";
    //     // $search_words = " あああ いいい ううう　ううう \"えええ えええ\" or 'おおお おおお' かか  きき　";
    //     // $search_words = " code=1 type_name=学校 'type_code1=sch ool'";
    //     // $search_words = "";
    //     // $search_words = null;

    //     // 正規表現の図) https://regexper.com/#%2F%5B%5Cs%5D*%5C%5C%5C%22%28%5B%5E%5C%5C%5C%22%5D%2B%29%5C%5C%5C%22%5B%5Cs%5D*%7C%22%20.%20%22%5B%5Cs%5D*'%28%5B%5E'%5D%2B%29'%5B%5Cs%5D*%7C%22%20.%20%22%5B%5Cs%5D%2B%2F
    //     // preg_split) https://www.php.net/manual/ja/function.preg-split.php
    //     //   PREG_SPLIT_NO_EMPTY: このフラグを設定すると、空文字列でないものだけが preg_split() により返されます。
    //     //   PREG_SPLIT_DELIM_CAPTURE: このフラグを設定すると、文字列分割用のパターン中の カッコ'()'によるサブパターンでキャプチャされた値も同時に返されます。
    //     //                             -> 正規表現にカッコ'()'でサブ抽出ができるようになる
    //     //
    //     //   ・半角空白(' ') でパースして配列を戻す
    //     //   ・半角空白有りでも、''か""で囲めば単語として抽出する
    //     //     ・前後の空白あり, 空白重複は取り除かれる
    //     //     ・重複ワードはそのまま, 全角空白はそのまま, %はそのまま
    //     //     ・null, "" でもarray空が戻ってきてくれる
    //     //     ・日本語OK
    //     $search_words_array = preg_split(
    //         "/[\s]*\\\"([^\\\"]+)\\\"[\s]*|" . "[\s]*'([^']+)'[\s]*|" . "[\s]+/",
    //         $search_words,
    //         0,     // -1|0=無制限(-1=default)
    //         PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
    //     );
    //     // print_r($search_words_array);

    //     if (preg_last_error() != PREG_NO_ERROR) {
    //         // エラーならdebug log出力
    //         // copy) https://www.php.net/manual/ja/function.preg-last-error.php#114105
    //         //   In PHP 5.5 and above, getting the error message is as simple as:
    //         $error_message = array_flip(get_defined_constants(true)['pcre'])[preg_last_error()];
    //         Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . '):' . $error_message);
    //     }

    //     return $search_words_array;
    // }
}
