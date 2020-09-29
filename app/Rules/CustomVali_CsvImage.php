<?php

namespace App\Rules;

/**
 * CSV用 画像バリデーション
 */
class CustomVali_CsvImage extends CustomVali_CsvExtensions
{
    protected $allow_extension = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $allow_extension = ['jpeg', 'png', 'gif', 'bmp', 'svg'])
    {
        $this->allow_extension = $allow_extension;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value 画像のフルパスセットする
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return parent::passes($attribute, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeには画像ファイルを指定してください。';
    }
}
