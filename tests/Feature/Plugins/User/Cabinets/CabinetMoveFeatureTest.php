<?php

namespace Tests\Feature\Plugins\User\Cabinets;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Cabinets\Cabinet;
use App\Models\User\Cabinets\CabinetContent;
use App\Models\Core\UsersRoles;
use App\User;

class CabinetMoveFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Featureテスト概要
     *
     * - URL経由で CabinetsPlugin の move アクションにPOSTし、
     *   リダイレクトやフラッシュメッセージ、セッションエラー内容を検証する。
     * - 成功ケースと主要な失敗ケース（移動先がファイル／別キャビネット／自身・子孫／同名重複）をカバー。
     */

    /**
     * 各テスト前のセットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();

        // DB Seeder を実行して初期データ(configs等)を投入
        $this->seed();
    }

    /**
     * ルートキャビネットを持つページ、バケツ、フレーム、キャビネット、ルートフォルダを作成して返す。
     *
     * @return array [$page, $bucket, $frame, $cabinet, $root]
     */
    private function makeRootCabinetWithFrame(): array
    {
        // Page / Buckets / Frame / Cabinet 準備
        $page = Page::factory()->create([
            'permanent_link' => 'test',
        ]);

        $bucket = Buckets::factory()->create([
            'bucket_name' => 'Test Cabinet',
            'plugin_name' => 'cabinets',
        ]);

        $frame = Frame::factory()->create([
            'page_id' => $page->id,
            'frame_title' => 'CabinetFrame',
            'plug_name' => 'cabinets',
            'bucket_id' => $bucket->id,
        ]);

        $cabinet = Cabinet::create([
            'bucket_id' => $bucket->id,
            'name' => 'Test Cabinet',
            'upload_max_size' => 1024,
        ]);

        // ルート
        $root = CabinetContent::create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => $cabinet->name,
            'is_folder' => CabinetContent::is_folder_on,
            'parent_id' => null,
        ]);

        return [$page, $bucket, $frame, $cabinet, $root];
    }

    /**
     * 正常系: ファイルをA→Bへ移動でき、件数と移動先名のフラッシュが出る。
     *
     * 前提:
     * - 同一キャビネット内にフォルダA/BとA配下のファイルを用意
     * - コンテンツ管理者権限のユーザーとしてPOST
     * 検証:
     * - 302リダイレクト（redirect_path）
     * - 対象ファイルの親IDがBへ更新
     * - フレーム用フラッシュメッセージに件数とBの名称を含む
     */
    public function testMoveFileToAnotherFolderSuccessAndFlashMessage()
    {
        [$page, $bucket, $frame, $cabinet, $root] = $this->makeRootCabinetWithFrame();

        // フォルダA/B と A配下のファイル
        $folderA = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'A',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        $folderB = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'B',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        $fileInA = $folderA->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => 1,
            'name' => 'sample.txt',
            'is_folder' => CabinetContent::is_folder_off,
        ]);

        // 権限のあるユーザー（コンテンツ管理者）を作成
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin',
        ]);

        // URL を叩く形式で移動実行
        $url = "/redirect/plugin/cabinets/move/{$page->id}/{$frame->id}";
        $redirect_path = "/plugin/cabinets/changeDirectory/{$page->id}/{$frame->id}/{$folderA->id}/#frame-{$frame->id}";
        $post_data = [
            'cabinet_content_id' => [$fileInA->id],
            'destination_id' => $folderB->id,
            'parent_id' => $folderA->id,
            'redirect_path' => $redirect_path,
        ];

        $response = $this->actingAs($user)->post($url, $post_data);

        // リダイレクト先
        $response->assertStatus(302);
        $response->assertRedirect($redirect_path);

        // 検証: 親がBに変わる
        $fileInA->refresh();
        $this->assertEquals($folderB->id, $fileInA->parent_id);

        // フラッシュメッセージが設定されている（件数と移動先名を含む）
        $expected_message = "選択した1件の項目を「{$folderB->name}」へ移動しました。";
        $response->assertSessionHas('flash_message_for_frame' . $frame->id, $expected_message);
    }

    /**
     * 失敗系: 移動先にファイルを指定した場合はバリデーションエラー。
     *
     * 検証:
     * - 302リダイレクト（redirect_path経由の戻り）
     * - destination_id に『移動先フォルダが不正です。』が格納される
     */
    public function testMoveFailsWhenDestinationIsFile(): void
    {
        [$page, $bucket, $frame, $cabinet, $root] = $this->makeRootCabinetWithFrame();

        $folderA = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'A',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        $fileDest = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => 1,
            'name' => 'not_folder.txt',
            'is_folder' => CabinetContent::is_folder_off,
        ]);
        $fileInA = $folderA->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => 2,
            'name' => 'sample.txt',
            'is_folder' => CabinetContent::is_folder_off,
        ]);

        $user = User::factory()->create();
        UsersRoles::factory()->create(['users_id' => $user->id, 'target' => 'base', 'role_name' => 'role_article_admin']);

        $url = "/redirect/plugin/cabinets/move/{$page->id}/{$frame->id}";
        $redirect_path = "/plugin/cabinets/changeDirectory/{$page->id}/{$frame->id}/{$folderA->id}/#frame-{$frame->id}";
        $post_data = [
            'cabinet_content_id' => [$fileInA->id],
            'destination_id' => $fileDest->id, // ファイルを指定（不正）
            'parent_id' => $folderA->id,
            'redirect_path' => $redirect_path,
        ];

        $response = $this->actingAs($user)->post($url, $post_data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['destination_id' => '移動先フォルダが不正です。']);
    }

    /**
     * 失敗系: 別キャビネットのフォルダを移動先にした場合はバリデーションエラー。
     *
     * 検証:
     * - 302リダイレクト
     * - destination_id に『移動先フォルダのキャビネットが一致しません。』
     */
    public function testMoveFailsWhenDestinationInDifferentCabinet(): void
    {
        [$page, $bucket, $frame, $cabinet, $root] = $this->makeRootCabinetWithFrame();

        // 同一キャビネット側
        $folderA = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'A',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        $fileInA = $folderA->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => 1,
            'name' => 'sample.txt',
            'is_folder' => CabinetContent::is_folder_off,
        ]);

        // 別キャビネットを用意（同一ページ/フレームに紐づける必要はない）
        $otherBucket = Buckets::factory()->create(['bucket_name' => 'Other', 'plugin_name' => 'cabinets']);
        $otherCabinet = Cabinet::create(['bucket_id' => $otherBucket->id, 'name' => 'OtherCab', 'upload_max_size' => 0]);
        $otherRoot = CabinetContent::create([
            'cabinet_id' => $otherCabinet->id,
            'upload_id' => null,
            'name' => $otherCabinet->name,
            'is_folder' => CabinetContent::is_folder_on,
            'parent_id' => null,
        ]);
        $destInOther = $otherRoot->children()->create([
            'cabinet_id' => $otherCabinet->id,
            'upload_id' => null,
            'name' => 'Dest',
            'is_folder' => CabinetContent::is_folder_on,
        ]);

        $user = User::factory()->create();
        UsersRoles::factory()->create(['users_id' => $user->id, 'target' => 'base', 'role_name' => 'role_article_admin']);

        $url = "/redirect/plugin/cabinets/move/{$page->id}/{$frame->id}";
        $redirect_path = "/plugin/cabinets/changeDirectory/{$page->id}/{$frame->id}/{$folderA->id}/#frame-{$frame->id}";
        $post_data = [
            'cabinet_content_id' => [$fileInA->id],
            'destination_id' => $destInOther->id, // 別キャビネット
            'parent_id' => $folderA->id,
            'redirect_path' => $redirect_path,
        ];

        $response = $this->actingAs($user)->post($url, $post_data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['destination_id' => '移動先フォルダのキャビネットが一致しません。']);
    }

    /**
     * 失敗系: 自身／子孫フォルダを移動先にした場合はバリデーションエラー。
     *
     * 検証:
     * - cabinet_content_id.0 に『自身または配下へは移動できません。』
     *   （子孫を指定したケース／自身を指定したケースの両方）
     */
    public function testMoveFailsWhenMovingIntoSelfOrDescendant(): void
    {
        [$page, $bucket, $frame, $cabinet, $root] = $this->makeRootCabinetWithFrame();

        $folderA = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'A',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        $child = $folderA->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'child',
            'is_folder' => CabinetContent::is_folder_on,
        ]);

        $user = User::factory()->create();
        UsersRoles::factory()->create(['users_id' => $user->id, 'target' => 'base', 'role_name' => 'role_article_admin']);

        $url = "/redirect/plugin/cabinets/move/{$page->id}/{$frame->id}";
        $redirect_path = "/plugin/cabinets/changeDirectory/{$page->id}/{$frame->id}/{$folderA->id}/#frame-{$frame->id}";

        // 子孫へ（NG）
        $response1 = $this->actingAs($user)->post($url, [
            'cabinet_content_id' => [$folderA->id],
            'destination_id' => $child->id,
            'parent_id' => $root->id,
            'redirect_path' => $redirect_path,
        ]);
        $response1->assertStatus(302);
        $response1->assertSessionHasErrors(['cabinet_content_id.0' => '自身または配下へは移動できません。']);

        // 自身へ（NG）
        $response2 = $this->actingAs($user)->post($url, [
            'cabinet_content_id' => [$folderA->id],
            'destination_id' => $folderA->id,
            'parent_id' => $root->id,
            'redirect_path' => $redirect_path,
        ]);
        $response2->assertStatus(302);
        $response2->assertSessionHasErrors(['cabinet_content_id.0' => '自身または配下へは移動できません。']);
    }

    /**
     * 失敗系: 移動先に同名のアイテムが既に存在する場合はバリデーションエラー。
     *
     * 検証:
     * - cabinet_content_id.0 に『移動先に同名のアイテムが存在します。』
     */
    public function testMoveFailsWhenDuplicateNameExistsInDestination(): void
    {
        [$page, $bucket, $frame, $cabinet, $root] = $this->makeRootCabinetWithFrame();

        $folderA = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'A',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        $folderB = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'B',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        // B側に同名ファイルを先に用意
        $folderB->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => 10,
            'name' => 'dup.txt',
            'is_folder' => CabinetContent::is_folder_off,
        ]);
        // A側から同名ファイルを移動しようとする
        $fileInA = $folderA->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => 11,
            'name' => 'dup.txt',
            'is_folder' => CabinetContent::is_folder_off,
        ]);

        $user = User::factory()->create();
        UsersRoles::factory()->create(['users_id' => $user->id, 'target' => 'base', 'role_name' => 'role_article_admin']);

        $url = "/redirect/plugin/cabinets/move/{$page->id}/{$frame->id}";
        $redirect_path = "/plugin/cabinets/changeDirectory/{$page->id}/{$frame->id}/{$folderA->id}/#frame-{$frame->id}";

        $response = $this->actingAs($user)->post($url, [
            'cabinet_content_id' => [$fileInA->id],
            'destination_id' => $folderB->id,
            'parent_id' => $folderA->id,
            'redirect_path' => $redirect_path,
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['cabinet_content_id.0' => '移動先に同名のアイテムが存在します。']);
    }

    /**
     * 正常系: 複数選択（ファイル＋フォルダ）を一度に移動できる。
     *
     * 検証:
     * - 302リダイレクト
     * - 選択した2件がともに移動先フォルダ配下になる
     * - フレーム用フラッシュに「2件」と移動先名が含まれる
     */
    public function testMoveMultipleItemsSuccess(): void
    {
        [$page, $bucket, $frame, $cabinet, $root] = $this->makeRootCabinetWithFrame();

        // A, B フォルダと A配下の ファイル＋サブフォルダ を用意
        $folderA = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'A',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        $folderB = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'B',
            'is_folder' => CabinetContent::is_folder_on,
        ]);
        $file1 = $folderA->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => 21,
            'name' => 'multi1.txt',
            'is_folder' => CabinetContent::is_folder_off,
        ]);
        $subFolder = $folderA->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => 'SubA',
            'is_folder' => CabinetContent::is_folder_on,
        ]);

        // 権限ユーザー
        $user = User::factory()->create();
        UsersRoles::factory()->create(['users_id' => $user->id, 'target' => 'base', 'role_name' => 'role_article_admin']);

        $url = "/redirect/plugin/cabinets/move/{$page->id}/{$frame->id}";
        $redirect_path = "/plugin/cabinets/changeDirectory/{$page->id}/{$frame->id}/{$folderA->id}/#frame-{$frame->id}";

        $response = $this->actingAs($user)->post($url, [
            'cabinet_content_id' => [$file1->id, $subFolder->id],
            'destination_id' => $folderB->id,
            'parent_id' => $folderA->id,
            'redirect_path' => $redirect_path,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect($redirect_path);

        $file1->refresh();
        $subFolder->refresh();
        $this->assertEquals($folderB->id, $file1->parent_id);
        $this->assertEquals($folderB->id, $subFolder->parent_id);

        $expected = "選択した2件の項目を「{$folderB->name}」へ移動しました。";
        $response->assertSessionHas('flash_message_for_frame' . $frame->id, $expected);
    }
}
