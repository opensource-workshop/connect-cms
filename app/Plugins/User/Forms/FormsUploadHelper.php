<?php

namespace App\Plugins\User\Forms;

use App\Enums\FormColumnType;
use App\Enums\UploadMaxSize;
use Illuminate\Http\UploadedFile;

/**
 * フォームのアップロード関連ユーティリティ。
 *
 * 拡張子/MIME/サイズ表示に関する処理を Blade・Plugin・Rule 間で
 * 共通化するための static ヘルパー。
 */
final class FormsUploadHelper
{
    /**
     * 拡張子を比較用に正規化する。
     *
     * 先頭ドットを除去し、小文字化して返す。
     *
     * @param mixed $extension
     */
    public static function normalizeExtension($extension): string
    {
        return mb_strtolower(ltrim((string) $extension, '.'));
    }

    /**
     * 拡張子入力を正規化して返す。
     *
     * 文字列入力は「半角/全角カンマ・空白」で分割し、
     * 空要素を除外した一意な拡張子配列に整形する。
     *
     * @param mixed $extensions
     * @return array<int, string>
     */
    public static function normalizeExtensions($extensions): array
    {
        if (is_null($extensions) || $extensions === '') {
            return [];
        }

        if (is_string($extensions)) {
            $extensions = preg_split('/[\s,，]+/u', $extensions, -1, PREG_SPLIT_NO_EMPTY);
        } elseif (! is_array($extensions)) {
            $extensions = [(string) $extensions];
        }

        $normalized = [];
        foreach ($extensions as $extension) {
            $extension = self::normalizeExtension($extension);
            if ($extension === '') {
                continue;
            }
            $normalized[] = $extension;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * 既定許可リストと項目設定値から、実際に使用する許可拡張子を返す。
     *
     * 項目設定値が空、または既定許可リストと交差しない場合は
     * 既定許可リストを返す。
     *
     * @param mixed $default_extensions
     * @param mixed $column_extensions
     * @return array<int, string>
     */
    public static function resolveAllowedExtensions($default_extensions, $column_extensions): array
    {
        $default_extensions = self::normalizeExtensions($default_extensions);
        $column_extensions = self::normalizeExtensions($column_extensions);

        if (empty($column_extensions)) {
            return $default_extensions;
        }

        $column_extensions = array_values(array_intersect($column_extensions, $default_extensions));
        return empty($column_extensions) ? $default_extensions : $column_extensions;
    }

    /**
     * accept属性文字列へ変換する。
     *
     * 例: ['jpg', 'png'] -> '.jpg, .png'
     *
     * @param array<int, string> $extensions
     */
    public static function toAcceptAttribute(array $extensions): string
    {
        $extensions = self::normalizeExtensions($extensions);

        $accept_extensions = array_map(function ($extension) {
            return '.' . $extension;
        }, $extensions);

        return implode(', ', $accept_extensions);
    }

    /**
     * キャプション内のアップロード最大サイズプレースホルダを置換する。
     *
     * `[[upload_max_filesize]]` を、列設定またはPHP設定に基づく
     * 表示用文字列へ置換し、改行は `nl2br()` で整形する。
     *
     * @param mixed $caption
     * @param mixed $form_column
     */
    public static function replaceUploadMaxFilesize($caption, $form_column): string
    {
        $max_filesize_caption = ini_get('upload_max_filesize');
        if (! empty($form_column) && ($form_column->column_type ?? null) == FormColumnType::file) {
            $rule_file_max_kb = $form_column->rule_file_max_kb ?? null;
            if (is_numeric($rule_file_max_kb) && (int) $rule_file_max_kb > 0) {
                $rule_file_max_kb = (string) ((int) $rule_file_max_kb);
                $upload_max_size_members = UploadMaxSize::getMembers();
                $max_filesize_caption = $upload_max_size_members[$rule_file_max_kb] ?? ($rule_file_max_kb . 'KB');
            }
        }

        return str_ireplace('[[upload_max_filesize]]', $max_filesize_caption, nl2br((string) $caption));
    }

    /**
     * 項目設定画面（ファイル型）で選択状態にする拡張子を返す。
     *
     * バリデーションエラー後の再表示時は old() を優先し、
     * 初期表示時は列設定値（未設定時は既定許可リスト全選択）を使用する。
     *
     * @param mixed $selected_extensions
     * @param mixed $is_old_submitted
     * @param mixed $column_rule_file_extensions
     * @param array<int, string> $file_extension_options
     * @return array<int, string>
     */
    public static function resolveSelectedExtensionsForEdit(
        $selected_extensions,
        $is_old_submitted,
        $column_rule_file_extensions,
        array $file_extension_options
    ): array {
        $file_extension_options = self::normalizeExtensions($file_extension_options);
        if (! is_null($selected_extensions) || ! empty($is_old_submitted)) {
            if (! is_array($selected_extensions)) {
                $selected_extensions = [];
            }
        } else {
            $selected_extensions = self::normalizeExtensions($column_rule_file_extensions);
            if (empty($selected_extensions)) {
                // 項目未設定時は既定値（許可リスト）を全てチェック状態にする。
                $selected_extensions = $file_extension_options;
            }
        }

        $selected_extensions = self::normalizeExtensions($selected_extensions);
        if (empty($selected_extensions) && empty($is_old_submitted)) {
            $selected_extensions = $file_extension_options;
        }

        return $selected_extensions;
    }

    /**
     * 項目設定画面（ファイル型）で表示するアップロード最大サイズ文字列を返す。
     *
     * `ini_get('upload_max_filesize')` の値をそのまま返す。
     */
    public static function getPhpUploadMaxFilesizeCaption(): string
    {
        return (string) ini_get('upload_max_filesize');
    }

    /**
     * 項目設定画面（ファイル型）で利用する最大サイズ選択値を正規化する。
     *
     * 未選択は空文字、選択済みは整数文字列へ統一する。
     *
     * @param mixed $selected_file_max_kb
     */
    public static function normalizeSelectedFileMaxKb($selected_file_max_kb): string
    {
        return ($selected_file_max_kb === null || $selected_file_max_kb === '')
            ? ''
            : (string) ((int) $selected_file_max_kb);
    }

    /**
     * 許可拡張子リストをカテゴリ表示用の配列へ整形する。
     *
     * カテゴリ未所属の拡張子は「その他」グループへ集約する。
     *
     * @param array<int, string> $file_extension_options
     * @param mixed $extension_categories
     * @return array<int, array{label: string, description: mixed, extensions: array<int, string>}>
     */
    public static function buildCategorizedExtensionGroups(
        array $file_extension_options,
        $extension_categories
    ): array {
        if (! is_array($extension_categories)) {
            $extension_categories = [];
        }

        $categorized_extension_groups = [];
        $categorized_extensions = [];
        foreach ($extension_categories as $extension_category) {
            if (! is_array($extension_category)) {
                continue;
            }

            $extensions = $extension_category['extensions'] ?? [];
            if (! is_array($extensions)) {
                continue;
            }

            $extensions = self::normalizeExtensions($extensions);
            $extensions = array_values(array_intersect($extensions, $file_extension_options));
            if (empty($extensions)) {
                continue;
            }

            $categorized_extension_groups[] = [
                'label' => (string) ($extension_category['label'] ?? 'その他'),
                'description' => $extension_category['description'] ?? null,
                'extensions' => $extensions,
            ];
            $categorized_extensions = array_merge($categorized_extensions, $extensions);
        }

        $categorized_extensions = array_values(array_unique($categorized_extensions));

        $uncategorized_extensions = array_values(array_diff($file_extension_options, $categorized_extensions));
        if (! empty($uncategorized_extensions)) {
            $categorized_extension_groups[] = [
                'label' => 'その他',
                'description' => null,
                'extensions' => $uncategorized_extensions,
            ];
        }

        return $categorized_extension_groups;
    }

    /**
     * PHP設定からアップロード上限（KB）を取得する。
     *
     * 取得不能・無効値の場合は `null` を返す。
     *
     * @return int|null
     */
    public static function getPhpUploadMaxKb(): ?int
    {
        $php_upload_max_bytes = UploadedFile::getMaxFilesize();
        if (! is_numeric($php_upload_max_bytes) || (float) $php_upload_max_bytes <= 0) {
            return null;
        }

        return max(1, (int) floor(((float) $php_upload_max_bytes) / 1024));
    }

    /**
     * 最大サイズ選択肢を構築する。
     *
     * enumの候補値を基に、PHP上限を超える値と無効値を除外する。
     *
     * @param int|null $php_upload_max_kb
     * @return array<string, string>
     */
    public static function buildMaxSizeOptions(?int $php_upload_max_kb): array
    {
        $max_size_options = [];
        foreach (UploadMaxSize::getMembers() as $size_kb => $size_label) {
            if ($size_kb === UploadMaxSize::infinity || ! is_numeric($size_kb)) {
                continue;
            }

            $size_kb = (int) $size_kb;
            if ($size_kb <= 0) {
                continue;
            }
            if (! empty($php_upload_max_kb) && $size_kb > $php_upload_max_kb) {
                continue;
            }

            $max_size_options[(string) $size_kb] = $size_label . '（' . $size_kb . 'KB）';
        }

        return $max_size_options;
    }
}
