<?php

namespace App\Rules;

/**
 * CSV用 画像バリデーション
 */
class CustomValiCsvImage extends CustomValiCsvExtensions
{
    protected $allow_extension = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     * @see \Illuminate\Validation\Concerns\ValidatesAttributes validateImage() copy by laravel validate image allow_extension
     */
    public function __construct(array $allow_extension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'svg', 'webp'])
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
