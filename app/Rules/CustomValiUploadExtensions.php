<?php

namespace App\Rules;

use App\Plugins\User\Forms\FormsUploadHelper;
use Illuminate\Contracts\Validation\Rule;

/**
 * アップロードファイルの拡張子許可リストチェック
 */
class CustomValiUploadExtensions implements Rule
{
    /** @var array<string> */
    private $allowed_extensions = [];

    /**
     * @param array<string> $allowed_extensions
     */
    public function __construct(array $allowed_extensions)
    {
        $this->allowed_extensions = FormsUploadHelper::normalizeExtensions($allowed_extensions);
    }

    /**
     * @param string $attribute
     * @param mixed $value
     */
    public function passes($attribute, $value): bool
    {
        // nullable向け。requiredは別ルールで判定される。
        if (empty($value)) {
            return true;
        }

        if (! method_exists($value, 'getClientOriginalExtension')) {
            return false;
        }

        $extension = FormsUploadHelper::normalizeExtension($value->getClientOriginalExtension());
        if ($extension === '') {
            return false;
        }

        return in_array($extension, $this->allowed_extensions, true);
    }

    /**
     * @return string
     */
    public function message()
    {
        return ':attributeには ' . implode(', ', $this->allowed_extensions) . ' のうちいずれかの拡張子を指定してください。';
    }
}
