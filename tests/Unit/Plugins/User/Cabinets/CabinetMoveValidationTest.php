<?php

namespace Tests\Unit\Plugins\User\Cabinets;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User\Cabinets\CabinetContent;
use App\Rules\CabinetValidDestinationFolder;
use App\Rules\CabinetSameCabinet;
use App\Rules\CabinetNotIntoDescendant;
use App\Rules\CabinetNoDuplicateNameInDestination;

/**
 * Cabinet 移動バリデーション用ルールの単体テスト。
 *
 * 対象ルール:
 * - CabinetValidDestinationFolder: 移動先が存在するフォルダで、同一キャビネット内であること
 * - CabinetSameCabinet: 移動対象が指定キャビネットに属していること
 * - CabinetNotIntoDescendant: 自身やその子孫フォルダを移動先にしないこと
 * - CabinetNoDuplicateNameInDestination: 移動先に同名の項目が存在しないこと
 *
 * それぞれのルールについて、通過/失敗ケースを最小構成のツリーで検証する。
 */
class CabinetMoveValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * フォルダ作成ヘルパー。親未指定時はルートとして作成する。
     */
    private function makeFolder(int $cabinetId, string $name, ?CabinetContent $parent = null): CabinetContent
    {
        $data = [
            'cabinet_id' => $cabinetId,
            'upload_id'  => null,
            'name'       => $name,
            'is_folder'  => CabinetContent::is_folder_on,
        ];
        if ($parent) {
            return $parent->children()->create($data);
        }
        return CabinetContent::create($data);
    }

    /**
     * ファイル作成ヘルパー。upload_id はダミー値を設定する。
     */
    private function makeFile(int $cabinetId, string $name, CabinetContent $parent): CabinetContent
    {
        return $parent->children()->create([
            'cabinet_id' => $cabinetId,
            'upload_id'  => 1, // dummy upload id
            'name'       => $name,
            'is_folder'  => CabinetContent::is_folder_off,
        ]);
    }

    /**
     * 同一キャビネット内のフォルダを移動先に指定した場合は通過する。
     */
    public function testCabinetValidDestinationFolderPassesForFolderInSameCabinet()
    {
        $root = $this->makeFolder(1, 'root');
        $dest = $this->makeFolder(1, 'dest', $root);

        $rule = new CabinetValidDestinationFolder(1);
        $this->assertTrue($rule->passes('destination_id', $dest->id));
    }

    /**
     * 移動先がフォルダでない（ファイル）場合は失敗する。
     */
    public function testCabinetValidDestinationFolderFailsIfNotFolder()
    {
        $root = $this->makeFolder(1, 'root');
        $file = $this->makeFile(1, 'file.txt', $root);

        $rule = new CabinetValidDestinationFolder(1);
        $this->assertFalse($rule->passes('destination_id', $file->id));
    }

    /**
     * 別キャビネットのフォルダを移動先に指定した場合は失敗する。
     */
    public function testCabinetValidDestinationFolderFailsIfDifferentCabinet()
    {
        $root1 = $this->makeFolder(1, 'root1');
        $root2 = $this->makeFolder(2, 'root2');
        $destInOther = $this->makeFolder(2, 'dest', $root2);

        $rule = new CabinetValidDestinationFolder(1);
        $this->assertFalse($rule->passes('destination_id', $destInOther->id));
    }

    /**
     * 移動対象が指定されたキャビネットに属していれば通過する。
     */
    public function testCabinetSameCabinetPasses()
    {
        $root = $this->makeFolder(1, 'root');
        $folder = $this->makeFolder(1, 'a', $root);

        $rule = new CabinetSameCabinet(1);
        $this->assertTrue($rule->passes('cabinet_content_id', $folder->id));
    }

    /**
     * 移動対象が別キャビネットに属している場合は失敗する。
     */
    public function testCabinetSameCabinetFails()
    {
        $root = $this->makeFolder(2, 'root');
        $folder = $this->makeFolder(2, 'a', $root);

        $rule = new CabinetSameCabinet(1);
        $this->assertFalse($rule->passes('cabinet_content_id', $folder->id));
    }

    /**
     * 自身の子孫フォルダを移動先に指定した場合は失敗する。
     */
    public function testCabinetNotIntoDescendantFailsForDescendant()
    {
        $root = $this->makeFolder(1, 'root');
        $parent = $this->makeFolder(1, 'parent', $root);
        $child = $this->makeFolder(1, 'child', $parent);

        $rule = new CabinetNotIntoDescendant($child->id);
        $this->assertFalse($rule->passes('cabinet_content_id', $parent->id));
    }

    /**
     * 自身を移動先に指定した場合は失敗する。
     */
    public function testCabinetNotIntoDescendantFailsForSelf()
    {
        $root = $this->makeFolder(1, 'root');
        $folder = $this->makeFolder(1, 'folder', $root);

        // 自身を移動先に指定した場合もNG
        $rule = new CabinetNotIntoDescendant($folder->id);
        $this->assertFalse($rule->passes('cabinet_content_id', $folder->id));
    }

    /**
     * 兄弟フォルダ間の移動であれば通過する。
     */
    public function testCabinetNotIntoDescendantPassesForSibling()
    {
        $root = $this->makeFolder(1, 'root');
        $a = $this->makeFolder(1, 'a', $root);
        $b = $this->makeFolder(1, 'b', $root);

        $rule = new CabinetNotIntoDescendant($b->id);
        $this->assertTrue($rule->passes('cabinet_content_id', $a->id));
    }

    /**
     * 移動先に同名のフォルダが既に存在する場合は失敗する。
     */
    public function testCabinetNoDuplicateNameInDestinationFailsWhenDuplicate()
    {
        $root = $this->makeFolder(1, 'root');
        $dest = $this->makeFolder(1, 'dest', $root);
        // duplicate exists in destination
        $this->makeFolder(1, 'same', $dest);
        $node = $this->makeFolder(1, 'same', $root);

        $rule = new CabinetNoDuplicateNameInDestination($dest->id);
        $this->assertFalse($rule->passes('cabinet_content_id', $node->id));
    }

    /**
     * 移動先に同名の項目が存在しなければ通過する。
     */
    public function testCabinetNoDuplicateNameInDestinationPassesWhenUnique()
    {
        $root = $this->makeFolder(1, 'root');
        $dest = $this->makeFolder(1, 'dest', $root);
        $node = $this->makeFolder(1, 'unique', $root);

        $rule = new CabinetNoDuplicateNameInDestination($dest->id);
        $this->assertTrue($rule->passes('cabinet_content_id', $node->id));
    }

    /**
     * 移動先に同名のファイルが存在する場合は失敗する。
     */
    public function testCabinetNoDuplicateNameInDestinationFailsWhenDuplicateFileName()
    {
        $root = $this->makeFolder(1, 'root');
        $dest = $this->makeFolder(1, 'dest', $root);
        // 既に同名ファイルが存在
        $this->makeFile(1, 'same.txt', $dest);
        // 移動対象（ファイル）
        $file = $this->makeFile(1, 'same.txt', $root);

        $rule = new CabinetNoDuplicateNameInDestination($dest->id);
        $this->assertFalse($rule->passes('cabinet_content_id', $file->id));
    }
}
