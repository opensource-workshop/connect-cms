<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Validation\Concerns\ValidatesAttributes;

/**
 * 複数カンマ区切りの年月入力チェック
 */
class CustomValiDatesYm implements Rule
{
    // Laravelのvalidateチェックメソッド
    use ValidatesAttributes;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // カンマ区切り文字列を配列に
        $dates_ym = explode(',', $value);

        foreach ($dates_ym as $date_ym) {
            // see) 年月の正規表現 https://regexper.com/#%2F%28%5B1-2%5D%5B0-9%5D%7B3%7D%29%5C%2F%28%5B0-1%5D%5B0-9%5D%29%2F
            // if (preg_match('/([1-2][0-9]{3})\/([0-1][0-9])/', trim($date_ym))) {
            //     echo '日付の形式が正しくありません。';
            //     return false;
            // }

            // laravel validate to date_format 年月チェック
            if (! $this->validateDateFormat($attribute, trim($date_ym), ['Y/m'])) {
                // echo '日付の形式が正しくありません。';
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // return ':attributeには正しい形式の日付を指定してください。';
        return ':attributeにはyyyy/mm形式で年月を入力してください。また複数入力したい場合は、カンマ「,」区切りで入力してください。';
    }
}
