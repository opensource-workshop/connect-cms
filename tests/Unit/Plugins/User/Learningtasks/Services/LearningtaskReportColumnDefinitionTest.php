<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Services;

use App\Enums\LearningtaskUseFunction;
use App\Plugins\User\Learningtasks\Services\LearningtaskReportColumnDefinition;
use App\Plugins\User\Learningtasks\Services\LearningtaskSettingChecker;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * LearningtaskReportColumnDefinition のユニットテストクラス
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Services\LearningtaskReportColumnDefinition
 */
class LearningtaskReportColumnDefinitionTest extends TestCase
{
    /**
     * 設定チェッカーのモックインスタンス
     * @var LearningtaskSettingChecker|MockInterface
     */
    private $mock_setting_checker;

    /**
     * 各テスト前にモックを準備
     */
    protected function setUp(): void
    {
        parent::setUp();
        // テストごとに SettingChecker のモックを生成
        $this->mock_setting_checker = Mockery::mock(LearningtaskSettingChecker::class);
    }

    /**
     * テスト対象クラスのインスタンスを生成するヘルパー
     * 指定された設定の isEnabled の振る舞いをモックに設定する
     *
     * @param array $settings ['setting_name' => bool, ...] 形式で設定の有効/無効を指定
     * @return LearningtaskReportColumnDefinition
     */
    private function createInstance(array $settings = []): LearningtaskReportColumnDefinition
    {
        // 指定された設定に基づいて isEnabled の戻り値を設定
        foreach ($settings as $setting_name => $is_enabled) {
            $this->mock_setting_checker
                ->shouldReceive('isEnabled')
                ->with($setting_name) // 特定の設定名に対する呼び出しを期待
                ->andReturn($is_enabled); // 指定された bool 値を返す
        }
        // 指定されなかった設定名に対して isEnabled が呼ばれた場合のデフォルト動作
        // 指定外は false を返すように設定しておく
        $this->mock_setting_checker->shouldReceive('isEnabled')->zeroOrMoreTimes()->andReturn(false);

        // モックを注入してテスト対象クラスのインスタンスを生成
        return new LearningtaskReportColumnDefinition($this->mock_setting_checker);
    }

    /**
     * isEnabled が全て無効の場合に基本ヘッダーのみを返すことをテスト
     * @test
     * @covers ::getHeaders
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getHeadersReturnsBaseHeadersWhenNoSettingsEnabled(): void
    {
        // Arrange: 全ての設定を無効にした状態でインスタンス生成
        $definition = $this->createInstance([
            LearningtaskUseFunction::use_report_comment => false,
            LearningtaskUseFunction::use_report_file => false,
            LearningtaskUseFunction::use_report_evaluate => false,
            LearningtaskUseFunction::use_report_evaluate_comment => false,
        ]);
        $expected_base_headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数'];

        // Act: ヘッダーを取得
        $headers = $definition->getHeaders();

        // Assert: 基本ヘッダーのみが返されることを確認
        $this->assertEquals($expected_base_headers, $headers);
    }

    /**
     * isEnabled が全て有効の場合に全てのヘッダーを返すことをテスト
     * @test
     * @covers ::getHeaders
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getHeadersReturnsCorrectHeadersWhenAllSettingsEnabled(): void
    {
        // Arrange: 全ての設定を有効にした状態でインスタンス生成
        $definition = $this->createInstance([
            LearningtaskUseFunction::use_report_comment => true,
            LearningtaskUseFunction::use_report_file => true,
            LearningtaskUseFunction::use_report_evaluate => true,
            LearningtaskUseFunction::use_report_evaluate_comment => true,
        ]);
        // 期待されるヘッダー（基本＋オプション全て）
        $expected_headers = [
            'ログインID', 'ユーザ名', '提出日時', '提出回数',
            '本文', 'ファイルURL', '評価', '評価コメント'
        ];

        // Act
        $headers = $definition->getHeaders();

        // Assert
        $this->assertEquals($expected_headers, $headers);
    }

    /**
     * isEnabled が一部有効の場合に正しいヘッダーを返すことをテスト
     * @test
     * @covers ::getHeaders
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getHeadersReturnsCorrectHeadersWhenSomeSettingsEnabled(): void
    {
         // Arrange: 一部の設定のみ有効 (評価と評価コメントのみ有効)
         $definition = $this->createInstance([
            LearningtaskUseFunction::use_report_evaluate => true,
            LearningtaskUseFunction::use_report_evaluate_comment => true,
            // 他はデフォルト(false)になるはず
         ]);
         // 期待されるヘッダー
         $expected_headers = [
            'ログインID', 'ユーザ名', '提出日時', '提出回数', // Base
            '評価', '評価コメント' // Enabled optional
         ];

        // Act
         $headers = $definition->getHeaders();

        // Assert
         $this->assertEquals($expected_headers, $headers);
    }

    /**
     * getColumnMap が期待される完全なマップ配列を返すことをテスト
     * @test
     * @covers ::getColumnMap
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getColumnMapReturnsExpectedArray(): void
    {
        // Arrange
        $definition = $this->createInstance(); // 設定内容は getColumnMap に影響しない
        // クラス内の COLUMN_MAP 定数と同じものが返るはず
        $expected_map = [
            'ログインID' => 'userid', 'ユーザ名' => 'username', '提出日時' => 'submitted_at',
            '提出回数' => 'submit_count', '本文' => 'report_comment', 'ファイルURL' => 'file_url',
            '評価' => 'grade', '評価コメント' => 'comment',
        ];

        // Act
        $map = $definition->getColumnMap();

        // Assert
        $this->assertEquals($expected_map, $map);
    }

    /**
     * getInternalKey がヘッダー名に対して正しい内部キーまたはnullを返すことをテスト
     * @test
     * @covers ::getInternalKey
     * @group learningtasks
     * @group learningtasks-coldef
     * @dataProvider internalKeyDataProvider
     */
    public function getInternalKeyReturnsCorrectKeyOrNull(string $header_name, ?string $expected_key): void
    {
        // Arrange
        $definition = $this->createInstance(); // 設定内容は影響しない

        // Act
        $key = $definition->getInternalKey($header_name);

        // Assert
        $this->assertEquals($expected_key, $key);
    }

    /**
     * getInternalKey テスト用のデータプロバイダ
     * @return array<string, array{header_name: string, expected_key: ?string}>
     */
    public function internalKeyDataProvider(): array
    {
        // テストケース名 => [引数, 期待値]
        return [
            'ログインID' => ['header_name' => 'ログインID', 'expected_key' => 'userid'],
            '評価コメント' => ['header_name' => '評価コメント', 'expected_key' => 'comment'],
            '存在しないヘッダー' => ['header_name' => '存在しない', 'expected_key' => null],
            '空文字ヘッダー' => ['header_name' => '', 'expected_key' => null],
        ];
    }

     /**
     * getValidationRulesBase が設定有効時に正しいルールを返すことをテスト
     * @test
     * @covers ::getValidationRulesBase
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getValidationRulesBaseReturnsCorrectRulesWhenSettingsEnabled(): void
    {
        // Arrange: 評価・評価コメントの両方が有効な設定
        $definition = $this->createInstance([
            LearningtaskUseFunction::use_report_evaluate => true,
            LearningtaskUseFunction::use_report_evaluate_comment => true,
        ]);

        // Act
        $rules = $definition->getValidationRulesBase();

        // Assert
        // userid ルールの確認
        $this->assertArrayHasKey('userid', $rules);
        $this->assertEquals(['required', 'string', 'exists:users,userid'], $rules['userid']);

        // grade ルールの確認 (有効時)
        $this->assertArrayHasKey('grade', $rules);
        // Rule::in はオブジェクトなので、単純比較ではなく内容を確認
        $this->assertContains('nullable', $rules['grade']);
        $this->assertContains('string', $rules['grade']);
        $this->assertInstanceOf(\Illuminate\Validation\Rules\In::class, $rules['grade'][2]); // Rule::in(...) が Rule オブジェクトであることを確認

        // comment ルールの確認 (有効時)
        $this->assertArrayHasKey('comment', $rules);
        $this->assertEquals(['nullable', 'string', 'max:65535', 'required_with:grade'], $rules['comment']);
    }

    /**
     * getValidationRulesBase が設定無効時に正しい(prohibited)ルールを返すことをテスト
     * @test
     * @covers ::getValidationRulesBase
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getValidationRulesBaseReturnsCorrectRulesWhenSettingsDisabled(): void
    {
         // Arrange: 評価・評価コメントの両方が無効な設定
         $definition = $this->createInstance([
             LearningtaskUseFunction::use_report_evaluate => false,
             LearningtaskUseFunction::use_report_evaluate_comment => false,
         ]);

         // Act
         $rules = $definition->getValidationRulesBase();

         // Assert
         $this->assertArrayHasKey('userid', $rules); // userid は常に存在
         // grade ルール (無効時)
         $this->assertArrayHasKey('grade', $rules);
         $this->assertEquals(['prohibited'], $rules['grade']);
         // comment ルール (無効時)
         $this->assertArrayHasKey('comment', $rules);
         $this->assertEquals(['prohibited'], $rules['comment']);
    }

    /**
     * getValidationRulesBase が評価ON/コメントOFF時に正しいルールを返すことをテスト
     * @test
     * @covers ::getValidationRulesBase
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getValidationRulesBaseReturnsCorrectRulesWhenEvaluateOnCommentOff(): void
    {
        // Arrange: 評価ON, コメントOFF
        $definition = $this->createInstance([
            LearningtaskUseFunction::use_report_evaluate => true,
            LearningtaskUseFunction::use_report_evaluate_comment => false,
        ]);
        // Act
        $rules = $definition->getValidationRulesBase();
        // Assert
        $this->assertArrayHasKey('grade', $rules);
        $this->assertIsObject($rules['grade'][2]); // Check Rule::in is object
        $this->assertContains('nullable', $rules['grade']);
        $this->assertArrayHasKey('comment', $rules);
        $this->assertEquals(['prohibited'], $rules['comment']); // コメントは禁止される
    }

    /**
     * getValidationRulesBase が評価OFF/コメントON時に正しいルールを返すことをテスト
     * @test
     * @covers ::getValidationRulesBase
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getValidationRulesBaseReturnsCorrectRulesWhenEvaluateOffCommentOn(): void
    {
         // Arrange: 評価OFF, コメントON
        $definition = $this->createInstance([
            LearningtaskUseFunction::use_report_evaluate => false,
            LearningtaskUseFunction::use_report_evaluate_comment => true,
        ]);
         // Act
        $rules = $definition->getValidationRulesBase();
         // Assert
        $this->assertArrayHasKey('grade', $rules);
        $this->assertEquals(['prohibited'], $rules['grade']); // 評価は禁止
        $this->assertArrayHasKey('comment', $rules);
        // コメントは有効だが、依存する grade が prohibited なので required_with は影響小
        $this->assertEquals(['nullable', 'string', 'max:65535', 'required_with:grade'], $rules['comment']);
    }

     /**
     * getValidationMessages が期待されるメッセージ配列を返すことをテスト
     * @test
     * @covers ::getValidationMessages
     * @group learningtasks
     * @group learningtasks-coldef
     */
    public function getValidationMessagesReturnsExpectedMessages(): void
    {
        // Arrange
        $definition = $this->createInstance(); // 設定はメッセージに影響しないと仮定
        // Act
        $messages = $definition->getValidationMessages();
        // Assert: 代表的なメッセージが存在し、内容が期待通りかを確認
        $this->assertArrayHasKey('userid.required', $messages);
        $this->assertEquals('ログインID列は必須です。', $messages['userid.required']);
        $this->assertArrayHasKey('grade.in', $messages);
        $this->assertEquals('評価列の値は A, B, C, D のいずれかである必要があります。', $messages['grade.in']);
        $this->assertArrayHasKey('comment.prohibited', $messages);
        $this->assertEquals('評価コメント機能が無効なため、評価コメント列は入力できません（空にしてください）。', $messages['comment.prohibited']);
        // 全てのメッセージを網羅的にテストしても良い
    }
}
