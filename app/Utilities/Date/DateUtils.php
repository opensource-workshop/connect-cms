<?php

namespace App\Utilities\Date;

// その他
use Carbon\Carbon;

class DateUtils
{
    /**
     * 渡された日付（数値オンリー、ハイフン付どちらも対応）に応じて「年」「年月」「年月日」フォーマットで返す
     *
     * @param  $value
     * @return yyyy年 | yyyy年m月 | yyyy年m月d日 | $valueそのまま
     */
    public static function formatDateJp($value)
    {
        $ret = '';
        if($value){
            // ハイフンを空文字に置換
            $value = str_replace('-', '', $value);
            // 全角→半角変換
            $value = mb_convert_kana($value, 'r');

            // (tips)checkdate(mm, dd, yy)
            if (mb_strlen($value) == 8) {
                // 8桁の場合
                if (checkdate(substr($value, 4, 2), substr($value, 6, 2), substr($value, 0, 4))) {
                    // 日付として正常
                    $ret = Carbon::parse($value)->format('Y年n月d日');
                } elseif (checkdate(substr($value, 4, 2), 1, substr($value, 0, 4))) {
                    // 8桁だが、ddの部分を01とすれば正常な場合は年月まで表示（日付部が00の場合を想定）
                    $ret = Carbon::parse(substr($value, 0, 4) . substr($value, 4, 2) . '01')->format('Y年n月');
                } else {
                    $ret = $value;
                }
            } elseif (mb_strlen($value) == 6) {
                // 6桁の場合
                if (checkdate(substr($value, 4, 2), 1, substr($value, 0, 4))) {
                    // 日付として正常
                    $ret = Carbon::parse(substr($value, 0, 4) . substr($value, 4, 2) . '01')->format('Y年n月');
                } else {
                    $ret = $value;
                }
            } elseif (mb_strlen($value) == 4) {
                // 4桁の場合
                if (checkdate(1, 1, substr($value, 0, 4))) {
                    // 日付として正常
                    $ret = Carbon::parse(substr($value, 0, 4) . '01' . '01')->format('Y年');
                } else {
                    $ret = $value;
                }
            } else {
                $ret = $value;
            }
        }

        return $ret;
    }
}
