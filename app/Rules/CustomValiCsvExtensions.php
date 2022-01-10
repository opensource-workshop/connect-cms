<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Filesystem\Filesystem;

/**
 * CSV用 拡張子バリデーション
 */
class CustomValiCsvExtensions implements Rule
{
    protected $allow_extension = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $allow_extension)
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
        // ファイルが存在するか
        if (! file_exists($value)) {
            // ファイルなしはエラー
            return false;
        }

        $filesystem = new Filesystem();
        $extension = $filesystem->extension($value);
        // 小文字に変換
        $extension = strtolower($extension);

        return in_array($extension, $this->allow_extension);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeには ' . implode(',', $this->allow_extension) . ' のうちいずれかの形式のファイルを指定してください。';
    }
}
