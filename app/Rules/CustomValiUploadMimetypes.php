<?php

namespace App\Rules;

use App\Plugins\User\Forms\FormsUploadHelper;
use Illuminate\Contracts\Validation\Rule;

/**
 * アップロードファイルのMIMEタイプ許可リストチェック
 */
class CustomValiUploadMimetypes implements Rule
{
    /** @var array<string, array<int, string>> */
    private $allowed_mimetype_map = [];

    /** @var array<string> */
    private $allowed_extensions = [];

    /**
     * @param array<string, array<int, string>|string> $allowed_mimetype_map
     * @param array<string> $allowed_extensions
     */
    public function __construct(array $allowed_mimetype_map, array $allowed_extensions = [])
    {
        $this->allowed_mimetype_map = $this->normalizeAllowedMimetypeMap($allowed_mimetype_map);
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

        if (! method_exists($value, 'getMimeType') || ! method_exists($value, 'getClientOriginalExtension')) {
            return false;
        }

        $extension = FormsUploadHelper::normalizeExtension($value->getClientOriginalExtension());
        if ($extension === '') {
            return false;
        }

        if (! empty($this->allowed_extensions) && ! in_array($extension, $this->allowed_extensions, true)) {
            return false;
        }

        if (! isset($this->allowed_mimetype_map[$extension])) {
            return false;
        }

        $detected_mimetype = $this->normalizeMimetype((string) $value->getMimeType());
        if ($detected_mimetype === '') {
            return false;
        }

        return in_array($detected_mimetype, $this->allowed_mimetype_map[$extension], true);
    }

    /**
     * @param array<string, array<int, string>|string> $allowed_mimetype_map
     * @return array<string, array<int, string>>
     */
    private function normalizeAllowedMimetypeMap(array $allowed_mimetype_map): array
    {
        $normalized_map = [];
        foreach ($allowed_mimetype_map as $extension => $mimetypes) {
            $normalized_extension = FormsUploadHelper::normalizeExtension($extension);
            if ($normalized_extension === '') {
                continue;
            }

            if (! is_array($mimetypes)) {
                $mimetypes = [$mimetypes];
            }

            $normalized_mimetypes = array_values(array_unique(array_filter(array_map(function ($mimetype) {
                return $this->normalizeMimetype((string) $mimetype);
            }, $mimetypes))));

            if (! empty($normalized_mimetypes)) {
                $normalized_map[$normalized_extension] = $normalized_mimetypes;
            }
        }

        return $normalized_map;
    }

    /**
     * MIMEタイプの比較用正規化
     */
    private function normalizeMimetype(string $mimetype): string
    {
        $mimetype = mb_strtolower(trim($mimetype));
        if ($mimetype === '') {
            return '';
        }

        $semicolon_pos = mb_strpos($mimetype, ';');
        if ($semicolon_pos !== false) {
            $mimetype = trim(mb_substr($mimetype, 0, $semicolon_pos));
        }

        return $mimetype;
    }

    /**
     * @return string
     */
    public function message()
    {
        if (! empty($this->allowed_extensions)) {
            $extensions = array_map(function ($extension) {
                return '.' . $extension;
            }, $this->allowed_extensions);

            return ':attributeには ' . implode(', ', $extensions) . ' のうちいずれかの形式を指定してください。';
        }

        return ':attributeのファイル形式が許可されていません。';
    }
}
