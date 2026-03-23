<?php

namespace Tests\Unit\Plugins\User\Forms;

use App\Enums\FormColumnType;
use App\Plugins\User\Forms\FormsUploadHelper;
use PHPUnit\Framework\TestCase;

/**
 * FormsUploadHelper のユニットテスト。
 */
class FormsUploadHelperTest extends TestCase
{
    /**
     * 文字列入力を区切り分解して拡張子を正規化できること。
     */
    public function testNormalizeExtensionsFromString(): void
    {
        $extensions = FormsUploadHelper::normalizeExtensions('.JPG, png，TXT  jpg');

        $this->assertSame(['jpg', 'png', 'txt'], $extensions);
    }

    /**
     * 項目設定値が既定許可外のみの場合は既定許可リストへフォールバックすること。
     */
    public function testResolveAllowedExtensionsFallsBackToDefaultWhenIntersectionIsEmpty(): void
    {
        $allowed_extensions = FormsUploadHelper::resolveAllowedExtensions(['jpg', 'png'], 'exe');

        $this->assertSame(['jpg', 'png'], $allowed_extensions);
    }

    /**
     * accept属性文字列へ変換できること。
     */
    public function testToAcceptAttributeBuildsAcceptString(): void
    {
        $accept_attr = FormsUploadHelper::toAcceptAttribute(['jpg', '.PNG']);

        $this->assertSame('.jpg, .png', $accept_attr);
    }

    /**
     * ファイル項目で列設定がある場合は項目設定の最大サイズ表記へ置換できること。
     */
    public function testReplaceUploadMaxFilesizeUsesColumnSettingForFileColumn(): void
    {
        $form_column = new \stdClass();
        $form_column->column_type = FormColumnType::file;
        $form_column->rule_file_max_kb = '2048';

        $caption = FormsUploadHelper::replaceUploadMaxFilesize('最大: [[upload_max_filesize]]', $form_column);

        $this->assertSame('最大: 2M', $caption);
    }

    /**
     * 旧入力がない場合は列設定の拡張子を選択状態にすること。
     */
    public function testResolveSelectedExtensionsForEditUsesColumnSettingByDefault(): void
    {
        $selected_extensions = FormsUploadHelper::resolveSelectedExtensionsForEdit(
            null,
            null,
            'jpg,png',
            ['jpg', 'png', 'pdf']
        );

        $this->assertSame(['jpg', 'png'], $selected_extensions);
    }

    /**
     * バリデーションエラー後に未選択で再表示された場合は選択状態を維持すること。
     */
    public function testResolveSelectedExtensionsForEditKeepsEmptySelectionAfterSubmitted(): void
    {
        $selected_extensions = FormsUploadHelper::resolveSelectedExtensionsForEdit(
            [],
            '1',
            'jpg,png',
            ['jpg', 'png']
        );

        $this->assertSame([], $selected_extensions);
    }

    /**
     * 拡張子カテゴリ未所属の項目は「その他」グループへまとめること。
     */
    public function testBuildCategorizedExtensionGroupsAddsOthersGroup(): void
    {
        $categorized_extension_groups = FormsUploadHelper::buildCategorizedExtensionGroups(
            ['jpg', 'png', 'pdf'],
            [
                [
                    'label' => '画像',
                    'description' => '画像カテゴリ',
                    'extensions' => ['jpg', 'png'],
                ],
            ]
        );

        $this->assertSame('画像', $categorized_extension_groups[0]['label']);
        $this->assertSame(['jpg', 'png'], $categorized_extension_groups[0]['extensions']);
        $this->assertSame('その他', $categorized_extension_groups[1]['label']);
        $this->assertSame(['pdf'], $categorized_extension_groups[1]['extensions']);
    }

    /**
     * 最大サイズ選択値をフォーム表示用に正規化できること。
     */
    public function testNormalizeSelectedFileMaxKb(): void
    {
        $this->assertSame('', FormsUploadHelper::normalizeSelectedFileMaxKb(''));
        $this->assertSame('2048', FormsUploadHelper::normalizeSelectedFileMaxKb('2048'));
    }
}
