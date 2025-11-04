<?php

namespace Tests\Unit\Plugins\Manage\UserManage;

use App\Enums\ConditionalOperator;
use App\Enums\Required;
use App\Enums\ShowType;
use App\Enums\UserColumnType;
use App\Models\Core\UsersColumns;
use App\Models\Core\UsersColumnsSet;
use App\Plugins\Manage\UserManage\UsersTool;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * UsersToolクラスのユニットテスト
 */
class UsersToolTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * getDefaultColumnAdditionalRules: 正規表現が設定されている場合、追加ルールが適用される
     *
     * @test
     */
    public function testGetDefaultColumnAdditionalRulesWithRegex()
    {
        // テスト用のUsersColumnsオブジェクトを作成
        $users_column = new UsersColumns();
        $users_column->rule_regex = '/^[a-zA-Z0-9]+$/';

        // 基本ルールを設定
        $base_rules = ['required', 'max:255'];

        // テスト実行
        $result = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column);

        // 検証: 正規表現ルールが追加されている
        $this->assertContains('regex:/^[a-zA-Z0-9]+$/', $result);
        // 検証: 基本ルールも保持されている
        $this->assertContains('required', $result);
        $this->assertContains('max:255', $result);
        // 検証: 配列の要素数が正しい（基本2つ + 追加1つ = 3つ）
        $this->assertCount(3, $result);
    }

    /**
     * getDefaultColumnAdditionalRules: 正規表現が未設定の場合、基本ルールのみ返す
     *
     * @test
     */
    public function testGetDefaultColumnAdditionalRulesWithoutRegex()
    {
        // テスト用のUsersColumnsオブジェクトを作成（正規表現なし）
        $users_column = new UsersColumns();
        $users_column->rule_regex = null;

        // 基本ルールを設定
        $base_rules = ['required', 'max:255'];

        // テスト実行
        $result = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column);

        // 検証: 基本ルールのみ返される
        $this->assertEquals($base_rules, $result);
        // 検証: 配列の要素数が基本ルールと同じ
        $this->assertCount(2, $result);
    }

    /**
     * getDefaultColumnAdditionalRules: 空の基本ルールでも正規表現が追加される
     *
     * @test
     */
    public function testGetDefaultColumnAdditionalRulesWithEmptyBaseRules()
    {
        // テスト用のUsersColumnsオブジェクトを作成
        $users_column = new UsersColumns();
        $users_column->rule_regex = '/^[0-9]{3}-[0-9]{4}$/';

        // 空の基本ルール
        $base_rules = [];

        // テスト実行
        $result = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column);

        // 検証: 正規表現ルールのみが含まれる
        $this->assertContains('regex:/^[0-9]{3}-[0-9]{4}$/', $result);
        $this->assertCount(1, $result);
    }

    /**
     * getDefaultColumnAdditionalRules: 複数の基本ルールと正規表現が正しくマージされる
     *
     * @test
     */
    public function testGetDefaultColumnAdditionalRulesWithMultipleRules()
    {
        // テスト用のUsersColumnsオブジェクトを作成
        $users_column = new UsersColumns();
        $users_column->rule_regex = '/^[a-z]+$/';

        // 複数の基本ルールを設定
        $base_rules = ['required', 'string', 'min:3', 'max:20'];

        // テスト実行
        $result = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column);

        // 検証: すべてのルールが含まれている
        $this->assertContains('required', $result);
        $this->assertContains('string', $result);
        $this->assertContains('min:3', $result);
        $this->assertContains('max:20', $result);
        $this->assertContains('regex:/^[a-z]+$/', $result);
        // 検証: 配列の要素数が正しい（基本4つ + 追加1つ = 5つ）
        $this->assertCount(5, $result);
    }

    /**
     * buildValidatorArray: デフォルト項目（user_name）に追加バリデーションが適用される
     *
     * @test
     */
    public function testBuildValidatorArrayAppliesRegexToUserName()
    {
        // テスト用のUsersColumnsコレクションを作成
        $users_column = new UsersColumns();
        $users_column->id = 1;
        $users_column->column_type = UserColumnType::user_name;
        $users_column->rule_regex = '/^[ぁ-んァ-ヶー一-龯]+$/u'; // 日本語のみ

        $users_columns = new Collection([$users_column]);

        // 基本バリデーション配列
        $validator_array = [
            'column' => [
                'name' => ['required', 'string', 'max:255'],
            ],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: 正規表現が追加されている
        $this->assertContains('regex:/^[ぁ-んァ-ヶー一-龯]+$/u', $result['column']['name']);
        // 検証: 基本ルールも保持されている
        $this->assertContains('required', $result['column']['name']);
        $this->assertContains('string', $result['column']['name']);
        $this->assertContains('max:255', $result['column']['name']);
    }

    /**
     * buildValidatorArray: デフォルト項目（login_id）に追加バリデーションが適用される
     *
     * @test
     */
    public function testBuildValidatorArrayAppliesRegexToLoginId()
    {
        // テスト用のUsersColumnsコレクションを作成
        $users_column = new UsersColumns();
        $users_column->id = 2;
        $users_column->column_type = UserColumnType::login_id;
        $users_column->rule_regex = '/^[a-zA-Z0-9_]+$/'; // 英数字とアンダースコアのみ

        $users_columns = new Collection([$users_column]);

        // 基本バリデーション配列（文字列形式でテスト）
        $validator_array = [
            'column' => [
                'userid' => 'required|max:255',
            ],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: 正規表現が追加されている
        $this->assertContains('regex:/^[a-zA-Z0-9_]+$/', $result['column']['userid']);
        // 検証: 文字列が配列に変換されている
        $this->assertIsArray($result['column']['userid']);
        // 検証: 基本ルールも保持されている
        $this->assertContains('required', $result['column']['userid']);
        $this->assertContains('max:255', $result['column']['userid']);
    }

    /**
     * buildValidatorArray: デフォルト項目（user_email）に追加バリデーションが適用される
     *
     * @test
     */
    public function testBuildValidatorArrayAppliesRegexToUserEmail()
    {
        // テスト用のUsersColumnsコレクションを作成
        $users_column = new UsersColumns();
        $users_column->id = 3;
        $users_column->column_type = UserColumnType::user_email;
        $users_column->rule_regex = '/^[a-zA-Z0-9._%+-]+@example\.com$/'; // example.comドメインのみ

        $users_columns = new Collection([$users_column]);

        // 基本バリデーション配列
        $validator_array = [
            'column' => [
                'email' => ['nullable', 'email', 'max:255'],
            ],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: 正規表現が追加されている
        $this->assertContains('regex:/^[a-zA-Z0-9._%+-]+@example\.com$/', $result['column']['email']);
        // 検証: 基本ルールも保持されている
        $this->assertContains('nullable', $result['column']['email']);
        $this->assertContains('email', $result['column']['email']);
        $this->assertContains('max:255', $result['column']['email']);
    }

    /**
     * buildValidatorArray: 正規表現が未設定のデフォルト項目は基本ルールのみ
     *
     * @test
     */
    public function testBuildValidatorArrayWithoutRegexKeepsBaseRules()
    {
        // テスト用のUsersColumnsコレクションを作成（正規表現なし）
        $users_column = new UsersColumns();
        $users_column->id = 1;
        $users_column->column_type = UserColumnType::user_name;
        $users_column->rule_regex = null;

        $users_columns = new Collection([$users_column]);

        // 基本バリデーション配列
        $base_name_rules = ['required', 'string', 'max:255'];
        $validator_array = [
            'column' => [
                'name' => $base_name_rules,
            ],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: 基本ルールのみが保持されている
        $this->assertEquals($base_name_rules, $result['column']['name']);
    }

    /**
     * buildValidatorArray: 複数のデフォルト項目を同時に処理できる
     *
     * @test
     */
    public function testBuildValidatorArrayWithMultipleDefaultColumns()
    {
        // 複数のデフォルト項目を作成
        $user_name_column = new UsersColumns();
        $user_name_column->id = 1;
        $user_name_column->column_type = UserColumnType::user_name;
        $user_name_column->rule_regex = '/^[ぁ-んァ-ヶー一-龯]+$/u';

        $login_id_column = new UsersColumns();
        $login_id_column->id = 2;
        $login_id_column->column_type = UserColumnType::login_id;
        $login_id_column->rule_regex = '/^[a-zA-Z0-9]+$/';

        $email_column = new UsersColumns();
        $email_column->id = 3;
        $email_column->column_type = UserColumnType::user_email;
        $email_column->rule_regex = null; // 正規表現なし

        $users_columns = new Collection([$user_name_column, $login_id_column, $email_column]);

        // 基本バリデーション配列
        $validator_array = [
            'column' => [
                'name' => ['required', 'string', 'max:255'],
                'userid' => ['required', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
            ],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: user_nameに正規表現が追加されている
        $this->assertContains('regex:/^[ぁ-んァ-ヶー一-龯]+$/u', $result['column']['name']);
        // 検証: login_idに正規表現が追加されている
        $this->assertContains('regex:/^[a-zA-Z0-9]+$/', $result['column']['userid']);
        // 検証: emailは正規表現なしで基本ルールのみ
        $email_rules_string = implode('|', $result['column']['email']);
        $this->assertStringNotContainsString('regex:', $email_rules_string);
        $this->assertContains('nullable', $result['column']['email']);
    }

    /**
     * buildValidatorArray: パスワード項目はスキップされる
     *
     * @test
     */
    public function testBuildValidatorArraySkipsPasswordColumn()
    {
        // パスワード項目を作成
        $password_column = new UsersColumns();
        $password_column->id = 4;
        $password_column->column_type = UserColumnType::user_password;
        $password_column->rule_regex = '/^.{8,}$/'; // 8文字以上（設定しても無視される）

        $users_columns = new Collection([$password_column]);

        // 基本バリデーション配列（パスワードフィールドなし）
        $validator_array = [
            'column' => [],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: パスワード項目は処理されず、columnは空のまま
        $this->assertEmpty($result['column']);
    }

    /**
     * buildValidatorArray: カスタム項目のバリデーションが適用される
     *
     * @test
     */
    public function testBuildValidatorArrayAppliesCustomColumnValidation()
    {
        // カスタム項目（テキスト）を作成
        $custom_column = new UsersColumns();
        $custom_column->id = 10;
        $custom_column->column_type = UserColumnType::text;
        $custom_column->column_name = 'custom_field';
        $custom_column->required = 1;
        $custom_column->rule_regex = '/^[0-9]{3}-[0-9]{4}$/'; // 郵便番号

        $users_columns = new Collection([$custom_column]);

        // 基本バリデーション配列
        $validator_array = [
            'column' => [],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: カスタム項目のバリデーションが追加されている
        // カスタム項目のキーは 'users_columns_value.{id}' の形式
        $this->assertArrayHasKey('users_columns_value.10', $result['column']);
        $this->assertContains('required', $result['column']['users_columns_value.10']);
        $this->assertContains('regex:/^[0-9]{3}-[0-9]{4}$/', $result['column']['users_columns_value.10']);
    }

    /**
     * buildValidatorArray: デフォルト項目とカスタム項目が混在する場合
     *
     * @test
     */
    public function testBuildValidatorArrayWithMixedColumns()
    {
        // デフォルト項目
        $user_name_column = new UsersColumns();
        $user_name_column->id = 1;
        $user_name_column->column_type = UserColumnType::user_name;
        $user_name_column->rule_regex = '/^[ぁ-んァ-ヶー一-龯]+$/u';

        // カスタム項目
        $custom_column = new UsersColumns();
        $custom_column->id = 10;
        $custom_column->column_type = UserColumnType::text;
        $custom_column->column_name = 'phone';
        $custom_column->required = 0;
        $custom_column->rule_regex = '/^[0-9]{2,4}-[0-9]{2,4}-[0-9]{4}$/'; // 電話番号

        $users_columns = new Collection([$user_name_column, $custom_column]);

        // 基本バリデーション配列
        $validator_array = [
            'column' => [
                'name' => ['required', 'string', 'max:255'],
            ],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: デフォルト項目が正しく処理されている
        $this->assertContains('regex:/^[ぁ-んァ-ヶー一-龯]+$/u', $result['column']['name']);
        // 検証: カスタム項目が追加されている（キーは 'users_columns_value.{id}'）
        $this->assertArrayHasKey('users_columns_value.10', $result['column']);
        $this->assertContains('regex:/^[0-9]{2,4}-[0-9]{2,4}-[0-9]{4}$/', $result['column']['users_columns_value.10']);
    }

    /**
     * buildValidatorArray: 空のコレクションでもエラーにならない
     *
     * @test
     */
    public function testBuildValidatorArrayWithEmptyCollection()
    {
        // 空のコレクション
        $users_columns = new Collection([]);

        // 基本バリデーション配列
        $validator_array = [
            'column' => [
                'name' => ['required', 'string', 'max:255'],
            ],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: 元の配列がそのまま返される
        $this->assertEquals($validator_array, $result);
    }

    /**
     * buildValidatorArray: base_rulesが存在しないデフォルト項目は処理されない
     *
     * @test
     */
    public function testBuildValidatorArrayWithoutBaseRulesForDefaultColumn()
    {
        // デフォルト項目を作成
        $user_name_column = new UsersColumns();
        $user_name_column->id = 1;
        $user_name_column->column_type = UserColumnType::user_name;
        $user_name_column->rule_regex = '/^[a-z]+$/';

        $users_columns = new Collection([$user_name_column]);

        // 基本バリデーション配列（nameフィールドなし）
        $validator_array = [
            'column' => [],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: base_rulesがないため、nameは追加されない
        $this->assertArrayNotHasKey('name', $result['column']);
    }

    /**
     * getDefaultColumnAdditionalRules: 実用的な正規表現パターン - 電話番号禁止
     *
     * @test
     */
    public function testRealWorldScenarioPreventPhoneNumberInLoginId()
    {
        // ログインIDに電話番号を禁止する正規表現
        $users_column = new UsersColumns();
        $users_column->rule_regex = '/^(?!.*[0-9]{3,4}-[0-9]{3,4}-[0-9]{4}).*$/';

        $base_rules = ['required', 'max:255'];

        // テスト実行
        $result = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column);

        // 検証: 正規表現が追加されている
        $this->assertContains('regex:/^(?!.*[0-9]{3,4}-[0-9]{3,4}-[0-9]{4}).*$/', $result);
        $this->assertCount(3, $result);
    }

    /**
     * getDefaultColumnAdditionalRules: 実用的な正規表現パターン - メールアドレス禁止
     *
     * @test
     */
    public function testRealWorldScenarioPreventEmailInLoginId()
    {
        // ログインIDにメールアドレス形式を禁止する正規表現
        $users_column = new UsersColumns();
        $users_column->rule_regex = '/^(?!.*@).*$/';

        $base_rules = ['required', 'max:255'];

        // テスト実行
        $result = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column);

        // 検証: 正規表現が追加されている
        $this->assertContains('regex:/^(?!.*@).*$/', $result);
    }

    /**
     * getDefaultColumnAdditionalRules: 実用的な正規表現パターン - 特定ドメインのみ許可
     *
     * @test
     */
    public function testRealWorldScenarioAllowOnlySpecificDomain()
    {
        // 特定ドメインのメールアドレスのみ許可
        $users_column = new UsersColumns();
        $users_column->rule_regex = '/^.+@(company\.com|company\.co\.jp)$/';

        $base_rules = ['nullable', 'email', 'max:255'];

        // テスト実行
        $result = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column);

        // 検証: 正規表現が追加されている
        $this->assertContains('regex:/^.+@(company\.com|company\.co\.jp)$/', $result);
        $this->assertCount(4, $result);
    }

    /**
     * getDefaultColumnAdditionalRules: 実用的な正規表現パターン - ひらがなのみ
     *
     * @test
     */
    public function testRealWorldScenarioOnlyHiragana()
    {
        // ユーザー名にひらがなのみ許可
        $users_column = new UsersColumns();
        $users_column->rule_regex = '/^[ぁ-ん]+$/u';

        $base_rules = ['required', 'string', 'max:255'];

        // テスト実行
        $result = UsersTool::getDefaultColumnAdditionalRules($base_rules, $users_column);

        // 検証: 正規表現が追加されている
        $this->assertContains('regex:/^[ぁ-ん]+$/u', $result);
    }

    /**
     * buildValidatorArray: user_id引数が正しく渡される（更新時）
     *
     * @test
     */
    public function testBuildValidatorArrayPassesUserIdForUpdate()
    {
        // カスタム項目を作成
        $custom_column = new UsersColumns();
        $custom_column->id = 10;
        $custom_column->column_type = UserColumnType::text;
        $custom_column->column_name = 'custom_text';
        $custom_column->required = 1;

        $users_columns = new Collection([$custom_column]);

        $validator_array = ['column' => []];
        $columns_set_id = 1;
        $user_id = 999; // 更新時のユーザーID

        // テスト実行（user_idを渡す）
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id, $user_id);

        // 検証: バリデーションルールが追加されている（内部でuser_idが使われる）
        // カスタム項目のキーは 'users_columns_value.{id}' の形式
        $this->assertArrayHasKey('users_columns_value.10', $result['column']);
        $this->assertContains('required', $result['column']['users_columns_value.10']);
    }

    /**
     * buildValidatorArray: 全デフォルト項目を一度に処理
     *
     * @test
     */
    public function testBuildValidatorArrayWithAllDefaultColumns()
    {
        // 全デフォルト項目を作成
        $columns = [
            'user_name' => UserColumnType::user_name,
            'login_id' => UserColumnType::login_id,
            'user_email' => UserColumnType::user_email,
        ];

        $users_columns_array = [];
        $id = 1;
        foreach ($columns as $key => $type) {
            $column = new UsersColumns();
            $column->id = $id++;
            $column->column_type = $type;
            $column->rule_regex = '/^test_' . $key . '$/';
            $users_columns_array[] = $column;
        }

        $users_columns = new Collection($users_columns_array);

        $validator_array = [
            'column' => [
                'name' => ['required', 'string', 'max:255'],
                'userid' => ['required', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
            ],
        ];

        $columns_set_id = 1;

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set_id);

        // 検証: 全デフォルト項目に正規表現が追加されている
        $this->assertContains('regex:/^test_user_name$/', $result['column']['name']);
        $this->assertContains('regex:/^test_login_id$/', $result['column']['userid']);
        $this->assertContains('regex:/^test_user_email$/', $result['column']['email']);
    }

    /**
     * UserColumnType::supportsValidationSettings: 正しいカラムタイプの配列を返す
     *
     * @test
     */
    public function testSupportsValidationSettingsReturnsCorrectTypes()
    {
        // テスト実行
        $result = UserColumnType::supportsValidationSettings();

        // 検証: 配列が返される
        $this->assertIsArray($result);

        // 検証: バリデーション設定をサポートする項目が含まれている
        $this->assertContains(UserColumnType::text, $result);
        $this->assertContains(UserColumnType::textarea, $result);
        $this->assertContains(UserColumnType::mail, $result);
        $this->assertContains(UserColumnType::user_name, $result);
        $this->assertContains(UserColumnType::login_id, $result);
        $this->assertContains(UserColumnType::user_email, $result);

        // 検証: サポートしない項目が含まれていない
        $this->assertNotContains(UserColumnType::radio, $result);
        $this->assertNotContains(UserColumnType::checkbox, $result);
        $this->assertNotContains(UserColumnType::select, $result);
        $this->assertNotContains(UserColumnType::user_password, $result);
        $this->assertNotContains(UserColumnType::created_at, $result);
        $this->assertNotContains(UserColumnType::updated_at, $result);
    }

    /**
     * UserColumnType::supportsValidationSettings: in_arrayで使用できる
     *
     * @test
     */
    public function testSupportsValidationSettingsWorksWithInArray()
    {
        $supported_types = UserColumnType::supportsValidationSettings();

        // 検証: サポートする項目はtrue
        $this->assertTrue(in_array(UserColumnType::text, $supported_types));
        $this->assertTrue(in_array(UserColumnType::user_name, $supported_types));
        $this->assertTrue(in_array(UserColumnType::login_id, $supported_types));

        // 検証: サポートしない項目はfalse
        $this->assertFalse(in_array(UserColumnType::radio, $supported_types));
        $this->assertFalse(in_array(UserColumnType::user_password, $supported_types));
        $this->assertFalse(in_array(UserColumnType::created_at, $supported_types));
    }

    /**
     * getConditionalDisplaySettings: 条件付き表示設定が正しく取得できる
     *
     * @test
     */
    public function testGetConditionalDisplaySettingsReturnsCorrectSettings()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // ターゲット項目を作成（条件付き表示あり）
        $target_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'ターゲット項目',
            'required' => Required::off,
            'display_sequence' => 2,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'テスト値',
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行
        $settings = UsersTool::getConditionalDisplaySettings($columns_set->id);

        // 検証: 配列が返される
        $this->assertIsArray($settings);
        $this->assertCount(1, $settings);

        // 検証: 正しい設定情報が含まれている
        $setting = $settings[0];
        $this->assertEquals($target_column->id, $setting['target_column_id']);
        $this->assertEquals($trigger_column->id, $setting['trigger_column_id']);
        $this->assertEquals(UserColumnType::text, $setting['trigger_column_type']);
        $this->assertEquals(ConditionalOperator::equals, $setting['operator']);
        $this->assertEquals('テスト値', $setting['value']);
    }

    /**
     * getConditionalDisplaySettings: 複数の条件付き表示設定を取得できる
     *
     * @test
     */
    public function testGetConditionalDisplaySettingsReturnsMultipleSettings()
    {
        $user = User::factory()->create();

        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // トリガー項目
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // ターゲット項目1
        $target_column1 = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'ターゲット項目1',
            'required' => Required::off,
            'display_sequence' => 2,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => '値1',
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // ターゲット項目2
        $target_column2 = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'ターゲット項目2',
            'required' => Required::off,
            'display_sequence' => 3,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::not_equals,
            'conditional_value' => '値2',
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行
        $settings = UsersTool::getConditionalDisplaySettings($columns_set->id);

        // 検証
        $this->assertCount(2, $settings);
        $this->assertEquals($target_column1->id, $settings[0]['target_column_id']);
        $this->assertEquals($target_column2->id, $settings[1]['target_column_id']);
        $this->assertEquals(ConditionalOperator::equals, $settings[0]['operator']);
        $this->assertEquals(ConditionalOperator::not_equals, $settings[1]['operator']);
    }

    /**
     * getConditionalDisplaySettings: is_empty演算子の場合もvalueがnullで取得できる
     *
     * @test
     */
    public function testGetConditionalDisplaySettingsWithIsEmptyOperator()
    {
        $user = User::factory()->create();

        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $target_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'ターゲット項目',
            'required' => Required::off,
            'display_sequence' => 2,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::is_empty,
            'conditional_value' => null,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行
        $settings = UsersTool::getConditionalDisplaySettings($columns_set->id);

        // 検証: is_empty演算子でもvalueがnullで取得できる
        $this->assertCount(1, $settings);
        $this->assertEquals(ConditionalOperator::is_empty, $settings[0]['operator']);
        $this->assertNull($settings[0]['value']);
    }

    /**
     * getConditionalDisplaySettings: システム固定項目をトリガーにした場合
     *
     * @test
     */
    public function testGetConditionalDisplaySettingsWithSystemFixedColumn()
    {
        $user = User::factory()->create();

        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // システム固定項目（氏名）をトリガーに
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::user_name,
            'column_name' => 'ユーザー名',
            'required' => Required::on,
            'display_sequence' => 0,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $target_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'ターゲット項目',
            'required' => Required::off,
            'display_sequence' => 1,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::is_not_empty,
            'conditional_value' => null,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行
        $settings = UsersTool::getConditionalDisplaySettings($columns_set->id);

        // 検証: システム固定項目のcolumn_typeが取得できる
        $this->assertCount(1, $settings);
        $this->assertEquals(UserColumnType::user_name, $settings[0]['trigger_column_type']);
    }

    /**
     * getConditionalDisplaySettings: 条件付き表示がOFFの項目は取得されない
     *
     * @test
     */
    public function testGetConditionalDisplaySettingsExcludesDisabledSettings()
    {
        $user = User::factory()->create();

        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // 条件付き表示OFF
        UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'ターゲット項目',
            'required' => Required::off,
            'display_sequence' => 2,
            'conditional_display_flag' => ShowType::not_show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'テスト値',
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行
        $settings = UsersTool::getConditionalDisplaySettings($columns_set->id);

        // 検証: 条件付き表示OFFの項目は取得されない
        $this->assertCount(0, $settings);
    }

    /**
     * getConditionalDisplaySettings: 空の結果を返す（設定なし）
     *
     * @test
     */
    public function testGetConditionalDisplaySettingsReturnsEmptyArrayWhenNoSettings()
    {
        $user = User::factory()->create();

        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // 条件付き表示設定なしの項目のみ
        UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '通常項目',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行
        $settings = UsersTool::getConditionalDisplaySettings($columns_set->id);

        // 検証: 空の配列が返される
        $this->assertIsArray($settings);
        $this->assertCount(0, $settings);
    }

    /**
     * getConditionalDisplaySettings: トリガー項目が削除されている場合
     *
     * @test
     */
    public function testGetConditionalDisplaySettingsWhenTriggerColumnDeleted()
    {
        $user = User::factory()->create();

        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // 存在しないトリガー項目IDを参照
        $target_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'ターゲット項目',
            'required' => Required::off,
            'display_sequence' => 2,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => 999999,  // 存在しないID
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'テスト値',
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行
        $settings = UsersTool::getConditionalDisplaySettings($columns_set->id);

        // 検証: trigger_column_typeがnullになる
        $this->assertCount(1, $settings);
        $this->assertNull($settings[0]['trigger_column_type']);
    }

    /**
     * hasCyclicDependency: 循環依存がない場合はfalseを返す
     *
     * @test
     */
    public function testHasCyclicDependencyReturnsFalseWhenNoCycle()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // 項目A、B、Cを作成（A→B→Cの依存関係）
        $column_a = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目A',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_b = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目B',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_a->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
            'display_sequence' => 2,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_c = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目C',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_b->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
            'display_sequence' => 3,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行: Aのトリガーとして新しい項目Dを設定しても循環しない
        $has_cycle = UsersTool::hasCyclicDependency($column_a->id, 999, $columns_set->id);

        // 検証: 循環依存なし
        $this->assertFalse($has_cycle);
    }

    /**
     * hasCyclicDependency: 直接的な循環依存を検出する（A→B→A）
     *
     * @test
     */
    public function testHasCyclicDependencyDetectsDirectCycle()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // 項目A、Bを作成
        $column_a = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目A',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_b = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目B',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_a->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
            'display_sequence' => 2,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行: AのトリガーとしてBを設定すると循環する（A→B→A）
        $has_cycle = UsersTool::hasCyclicDependency($column_a->id, $column_b->id, $columns_set->id);

        // 検証: 循環依存あり
        $this->assertTrue($has_cycle);
    }

    /**
     * hasCyclicDependency: 間接的な循環依存を検出する（A→B→C→A）
     *
     * @test
     */
    public function testHasCyclicDependencyDetectsIndirectCycle()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // 項目A、B、Cを作成（B→C、C→Aと設定）
        $column_a = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目A',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_b = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目B',
            'required' => Required::off,
            'display_sequence' => 2,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_c = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目C',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_b->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
            'display_sequence' => 3,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // Cのトリガーとして、さらにAを設定
        $column_a->update([
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_c->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
        ]);

        // テスト実行: BのトリガーとしてAを設定すると循環する（B→A→C→B）
        $has_cycle = UsersTool::hasCyclicDependency($column_b->id, $column_a->id, $columns_set->id);

        // 検証: 循環依存あり
        $this->assertTrue($has_cycle);
    }

    /**
     * hasCyclicDependency: トリガーが未設定の場合はfalseを返す
     *
     * @test
     */
    public function testHasCyclicDependencyReturnsFalseWhenNoTrigger()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // 項目Aを作成
        $column_a = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目A',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // テスト実行: トリガーがnullの場合
        $has_cycle = UsersTool::hasCyclicDependency($column_a->id, null, $columns_set->id);

        // 検証: 循環依存なし
        $this->assertFalse($has_cycle);
    }

    /**
     * hasCyclicDependency: 複雑な依存関係でも循環を正しく検出する
     *
     * @test
     */
    public function testHasCyclicDependencyDetectsComplexCycle()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // 項目A, B, C, D, Eを作成（A→B→D、C→D、D→Eの依存関係）
        $column_a = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目A',
            'required' => Required::off,
            'display_sequence' => 1,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_b = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目B',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_a->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
            'display_sequence' => 2,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_c = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目C',
            'required' => Required::off,
            'display_sequence' => 3,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_d = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目D',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_b->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
            'display_sequence' => 4,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        $column_e = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目E',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_d->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
            'display_sequence' => 5,
            'created_id' => $user->id,
            'updated_id' => $user->id,
        ]);

        // Cのトリガーとして、Dを設定（C→D）
        $column_c->update([
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_d->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'test',
        ]);

        // テスト実行1: EのトリガーとしてCを設定しても循環しない（E→C→D→B→Aで終わり）
        $has_cycle1 = UsersTool::hasCyclicDependency($column_e->id, $column_c->id, $columns_set->id);
        $this->assertFalse($has_cycle1);

        // テスト実行2: AのトリガーとしてEを設定すると循環する（A→E→D→B→A）
        $has_cycle2 = UsersTool::hasCyclicDependency($column_a->id, $column_e->id, $columns_set->id);
        $this->assertTrue($has_cycle2);
    }

    /**
     * isColumnDisplayed: 条件付き表示がOFFの場合は常に表示される
     *
     * @test
     */
    public function testIsColumnDisplayedReturnsTrueWhenConditionalDisplayIsOff()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // 条件付き表示がOFFの項目を作成
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::not_show,
        ]);

        // リクエストデータ（ダミー）
        $request_data = [];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 条件付き表示がOFFなので常に表示される
        $this->assertTrue($result);
    }

    /**
     * isColumnDisplayed: トリガー項目がない場合は常に表示される
     *
     * @test
     */
    public function testIsColumnDisplayedReturnsTrueWhenNoTrigger()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // 条件付き表示がONだがトリガーIDがnullの項目
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => null,
        ]);

        // リクエストデータ（ダミー）
        $request_data = [];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: トリガーがないので常に表示される
        $this->assertTrue($result);
    }

    /**
     * isColumnDisplayed: equals演算子で条件を満たす場合は表示される
     *
     * @test
     */
    public function testIsColumnDisplayedWithEqualsOperatorReturnsTrue()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（トリガー項目 = "はい" の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        // リクエストデータ（トリガー項目 = "はい"）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => 'はい',
            ],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 条件を満たすので表示される
        $this->assertTrue($result);
    }

    /**
     * isColumnDisplayed: equals演算子で条件を満たさない場合は非表示
     *
     * @test
     */
    public function testIsColumnDisplayedWithEqualsOperatorReturnsFalse()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（トリガー項目 = "はい" の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        // リクエストデータ（トリガー項目 = "いいえ"）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => 'いいえ',
            ],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 条件を満たさないので非表示
        $this->assertFalse($result);
    }

    /**
     * isColumnDisplayed: not_equals演算子が正しく動作する
     *
     * @test
     */
    public function testIsColumnDisplayedWithNotEqualsOperator()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（トリガー項目 != "はい" の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::not_equals,
            'conditional_value' => 'はい',
        ]);

        // リクエストデータ（トリガー項目 = "いいえ"）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => 'いいえ',
            ],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 条件を満たす（"いいえ" != "はい"）ので表示される
        $this->assertTrue($result);
    }

    /**
     * isColumnDisplayed: is_empty演算子が正しく動作する
     *
     * @test
     */
    public function testIsColumnDisplayedWithIsEmptyOperator()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（トリガー項目が空の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::is_empty,
            'conditional_value' => null,
        ]);

        // テストケース1: 空文字の場合
        $request_data1 = [
            'users_columns_value' => [
                $trigger_column->id => '',
            ],
        ];
        $result1 = UsersTool::isColumnDisplayed($column, $request_data1);
        $this->assertTrue($result1);

        // テストケース2: 値がある場合
        $request_data2 = [
            'users_columns_value' => [
                $trigger_column->id => 'test',
            ],
        ];
        $result2 = UsersTool::isColumnDisplayed($column, $request_data2);
        $this->assertFalse($result2);
    }

    /**
     * isColumnDisplayed: is_not_empty演算子が正しく動作する
     *
     * @test
     */
    public function testIsColumnDisplayedWithIsNotEmptyOperator()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（トリガー項目が空でない時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::is_not_empty,
            'conditional_value' => null,
        ]);

        // テストケース1: 値がある場合
        $request_data1 = [
            'users_columns_value' => [
                $trigger_column->id => 'test',
            ],
        ];
        $result1 = UsersTool::isColumnDisplayed($column, $request_data1);
        $this->assertTrue($result1);

        // テストケース2: 空文字の場合
        $request_data2 = [
            'users_columns_value' => [
                $trigger_column->id => '',
            ],
        ];
        $result2 = UsersTool::isColumnDisplayed($column, $request_data2);
        $this->assertFalse($result2);
    }

    /**
     * isColumnDisplayed: システム固定項目をトリガーにできる
     *
     * @test
     */
    public function testIsColumnDisplayedWithSystemFixedColumnAsTrigger()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目（システム固定項目: ユーザー名）を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::user_name,
            'column_name' => 'ユーザー名',
            'required' => Required::on,
        ]);

        // 条件付き表示項目を作成（ユーザー名 = "admin" の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'admin',
        ]);

        // リクエストデータ（ユーザー名 = "admin"）
        $request_data = [
            'name' => 'admin',
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 条件を満たすので表示される
        $this->assertTrue($result);
    }

    /**
     * isColumnDisplayed: チェックボックス（配列）が正しく動作する
     *
     * @test
     */
    public function testIsColumnDisplayedWithCheckboxArray()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目（チェックボックス）を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::checkbox,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（チェックボックスが "選択肢1,選択肢2" の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => '選択肢1,選択肢2',
        ]);

        // リクエストデータ（チェックボックス配列）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => ['選択肢1', '選択肢2'],
            ],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 条件を満たすので表示される
        $this->assertTrue($result);
    }

    /**
     * buildValidatorArray: 条件付き表示で非表示の項目はバリデーションをスキップする
     *
     * @test
     */
    public function testBuildValidatorArraySkipsHiddenConditionalColumns()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目（必須）を作成
        $conditional_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '条件付き項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        $users_columns = collect([$trigger_column, $conditional_column]);

        // リクエストデータ（トリガー = "いいえ" なので条件付き項目は非表示）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => 'いいえ',
            ],
        ];

        // バリデーション配列の初期値
        $validator_array = [
            'column' => [],
            'message' => [],
        ];

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set->id, null, $request_data);

        // 検証: 条件付き項目のバリデーションルールが追加されていない
        $this->assertArrayNotHasKey('users_columns_value.' . $conditional_column->id, $result['column']);
    }

    /**
     * buildValidatorArray: 条件付き表示で表示される項目はバリデーションが適用される
     *
     * @test
     */
    public function testBuildValidatorArrayIncludesVisibleConditionalColumns()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目（必須）を作成
        $conditional_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '条件付き項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        $users_columns = collect([$trigger_column, $conditional_column]);

        // リクエストデータ（トリガー = "はい" なので条件付き項目は表示）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => 'はい',
            ],
        ];

        // バリデーション配列の初期値
        $validator_array = [
            'column' => [],
            'message' => [],
        ];

        // テスト実行
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set->id, null, $request_data);

        // 検証: 条件付き項目のバリデーションルールが追加されている
        $this->assertArrayHasKey('users_columns_value.' . $conditional_column->id, $result['column']);
        $this->assertContains('required', $result['column']['users_columns_value.' . $conditional_column->id]);
    }

    /**
     * getConditionalDisplaySettings: requiredフラグが含まれる
     *
     * @test
     */
    public function testGetConditionalDisplaySettingsIncludesRequiredFlag()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目（必須ON）を作成
        $column_required = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '必須項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        // 条件付き表示項目（必須OFF）を作成
        $column_not_required = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '任意項目',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        // テスト実行
        $settings = UsersTool::getConditionalDisplaySettings($columns_set->id);

        // 検証: 2つの設定が取得される
        $this->assertCount(2, $settings);

        // 必須項目の設定にrequired=trueが含まれる
        $required_setting = collect($settings)->firstWhere('target_column_id', $column_required->id);
        $this->assertTrue($required_setting['required']);

        // 任意項目の設定にrequired=falseが含まれる
        $not_required_setting = collect($settings)->firstWhere('target_column_id', $column_not_required->id);
        $this->assertFalse($not_required_setting['required']);
    }

    /**
     * normalizeCommaSeparatedValue: カンマ区切り文字列をソートして正規化
     *
     * @test
     */
    public function testNormalizeCommaSeparatedValueSortsItems()
    {
        // ソート前の値
        $input = '選択肢2,選択肢1,選択肢3';

        // 正規化（ソート）
        $result = UsersTool::normalizeCommaSeparatedValue($input);

        // ソート済みになっていることを検証
        $this->assertEquals('選択肢1,選択肢2,選択肢3', $result);
    }

    /**
     * normalizeCommaSeparatedValue: 空白を削除して正規化
     *
     * @test
     */
    public function testNormalizeCommaSeparatedValueTrimsWhitespace()
    {
        // 空白を含む値
        $input = ' 選択肢1 , 選択肢2 , 選択肢3 ';

        // 正規化（空白削除とソート）
        $result = UsersTool::normalizeCommaSeparatedValue($input);

        // 空白が削除されソート済みになっていることを検証
        $this->assertEquals('選択肢1,選択肢2,選択肢3', $result);
    }

    /**
     * normalizeCommaSeparatedValue: 空文字列を除外
     *
     * @test
     */
    public function testNormalizeCommaSeparatedValueRemovesEmptyItems()
    {
        // 空文字列を含む値
        $input = '選択肢1,,選択肢2,';

        // 正規化（空文字列除外とソート）
        $result = UsersTool::normalizeCommaSeparatedValue($input);

        // 空文字列が除外されていることを検証
        $this->assertEquals('選択肢1,選択肢2', $result);
    }

    /**
     * normalizeCommaSeparatedValue: nullと空文字列をそのまま返す
     *
     * @test
     */
    public function testNormalizeCommaSeparatedValueHandlesNullAndEmpty()
    {
        // nullの場合
        $result_null = UsersTool::normalizeCommaSeparatedValue(null);
        $this->assertNull($result_null);

        // 空文字列の場合
        $result_empty = UsersTool::normalizeCommaSeparatedValue('');
        $this->assertEquals('', $result_empty);
    }

    /**
     * normalizeCommaSeparatedValue: 単一の値はそのまま返す
     *
     * @test
     */
    public function testNormalizeCommaSeparatedValueHandlesSingleValue()
    {
        // 単一の値
        $input = '選択肢1';

        // 正規化
        $result = UsersTool::normalizeCommaSeparatedValue($input);

        // そのまま返されることを検証
        $this->assertEquals('選択肢1', $result);
    }

    /**
     * normalizeCommaSeparatedValue: 実際のチェックボックス配列との比較
     *
     * @test
     */
    public function testNormalizeCommaSeparatedValueMatchesCheckboxArray()
    {
        // ユーザーが選択した順序（配列）
        $checkbox_array1 = ['選択肢2', '選択肢1', '選択肢3'];
        $checkbox_array2 = ['選択肢1', '選択肢3', '選択肢2'];

        // 配列をソートしてカンマ区切りに変換（getTriggerValueの処理を模倣）
        sort($checkbox_array1);
        $trigger_value1 = implode(',', $checkbox_array1);

        sort($checkbox_array2);
        $trigger_value2 = implode(',', $checkbox_array2);

        // 条件値（管理者が設定した値）
        $condition_value = '選択肢2,選択肢1,選択肢3';
        $normalized_condition = UsersTool::normalizeCommaSeparatedValue($condition_value);

        // 選択順序に関わらず同じ値になることを検証
        $this->assertEquals($trigger_value1, $trigger_value2);
        $this->assertEquals($trigger_value1, $normalized_condition);
        $this->assertEquals('選択肢1,選択肢2,選択肢3', $normalized_condition);
    }

    /**
     * isColumnDisplayed: システム固定項目（ユーザー名）がnullの場合
     *
     * @test
     */
    public function testIsColumnDisplayedWithSystemFixedColumnNullValue()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目（システム固定項目: ユーザー名）を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::user_name,
            'column_name' => 'ユーザー名',
            'required' => Required::on,
        ]);

        // 条件付き表示項目を作成（ユーザー名が空でない時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::is_not_empty,
            'conditional_value' => null,
        ]);

        // リクエストデータ（ユーザー名がnull）
        $request_data = [
            'name' => null,
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: ユーザー名がnullなので非表示
        $this->assertFalse($result);
    }

    /**
     * isColumnDisplayed: システム固定項目（ログインID）が空文字の場合
     *
     * @test
     */
    public function testIsColumnDisplayedWithSystemFixedColumnEmptyString()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目（システム固定項目: ログインID）を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::login_id,
            'column_name' => 'ログインID',
            'required' => Required::on,
        ]);

        // 条件付き表示項目を作成（ログインIDが空の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::is_empty,
            'conditional_value' => null,
        ]);

        // リクエストデータ（ログインIDが空文字）
        $request_data = [
            'userid' => '',
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: ログインIDが空文字なので表示
        $this->assertTrue($result);
    }

    /**
     * isColumnDisplayed: チェックボックスで一部のみ選択された場合
     *
     * @test
     */
    public function testIsColumnDisplayedWithPartialCheckboxSelection()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目（チェックボックス）を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::checkbox,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（チェックボックスが "選択肢1,選択肢2,選択肢3" の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => '選択肢1,選択肢2,選択肢3',
        ]);

        // リクエストデータ（一部のみ選択: 選択肢1と選択肢2のみ）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => ['選択肢1', '選択肢2'],
            ],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 一部のみ選択なので条件を満たさず非表示
        $this->assertFalse($result);
    }

    /**
     * isColumnDisplayed: 条件値が空文字でequals演算子の場合
     *
     * @test
     */
    public function testIsColumnDisplayedWithEmptyStringEqualsOperator()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（トリガー項目 = "" の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => '',
        ]);

        // リクエストデータ（トリガー項目が空文字）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => '',
            ],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 空文字 == 空文字 なので表示される
        $this->assertTrue($result);
    }

    /**
     * isColumnDisplayed: 数値の0がis_emptyで正しく評価される
     *
     * @test
     */
    public function testIsColumnDisplayedWithZeroValueIsEmpty()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（トリガー項目が空の時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::is_empty,
            'conditional_value' => null,
        ]);

        // リクエストデータ（トリガー項目が数値の0）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => 0,
            ],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: 0は空ではない（厳密な判定）ので非表示
        $this->assertFalse($result);
    }

    /**
     * isColumnDisplayed: 文字列の"0"がis_not_emptyで正しく評価される
     *
     * @test
     */
    public function testIsColumnDisplayedWithStringZeroIsNotEmpty()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成（トリガー項目が空でない時に表示）
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::is_not_empty,
            'conditional_value' => null,
        ]);

        // リクエストデータ（トリガー項目が文字列の"0"）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => '0',
            ],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: "0"は空ではない（厳密な判定）ので表示
        $this->assertTrue($result);
    }

    /**
     * isColumnDisplayed: ネストした条件付き表示（A→B→C）
     *
     * @test
     */
    public function testIsColumnDisplayedWithNestedConditions()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目A（常に表示）
        $column_a = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => '項目A',
            'required' => Required::off,
        ]);

        // トリガー項目B（Aが"はい"の時に表示）
        $column_b = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => '項目B',
            'required' => Required::off,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_a->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        // 項目C（Bが"はい"の時に表示）
        $column_c = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '項目C',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $column_b->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        // シナリオ1: A="はい", B="はい" → Cは表示
        $request_data1 = [
            'users_columns_value' => [
                $column_a->id => 'はい',
                $column_b->id => 'はい',
            ],
        ];
        $result1 = UsersTool::isColumnDisplayed($column_c, $request_data1);
        $this->assertTrue($result1);

        // シナリオ2: A="はい", B="いいえ" → Cは非表示
        $request_data2 = [
            'users_columns_value' => [
                $column_a->id => 'はい',
                $column_b->id => 'いいえ',
            ],
        ];
        $result2 = UsersTool::isColumnDisplayed($column_c, $request_data2);
        $this->assertFalse($result2);

        // シナリオ3: A="いいえ", B未入力 → Cは非表示
        $request_data3 = [
            'users_columns_value' => [
                $column_a->id => 'いいえ',
            ],
        ];
        $result3 = UsersTool::isColumnDisplayed($column_c, $request_data3);
        $this->assertFalse($result3);
    }

    /**
     * buildValidatorArray: 複数の条件付き表示項目が正しく処理される
     *
     * @test
     */
    public function testBuildValidatorArrayWithMultipleConditionalColumns()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目1（表示される）
        $column1 = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '条件付き項目1',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        // 条件付き表示項目2（非表示）
        $column2 = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => '条件付き項目2',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'いいえ',
        ]);

        // リクエストデータ（トリガー項目 = "はい"）
        $request_data = [
            'users_columns_value' => [
                $trigger_column->id => 'はい',
            ],
        ];

        // バリデーション配列を構築
        $validator_array = ['column' => []];
        $users_columns = collect([$column1, $column2]);
        $result = UsersTool::buildValidatorArray($validator_array, $users_columns, $columns_set->id, null, $request_data);

        // 検証: 項目1はバリデーションに含まれる
        $this->assertArrayHasKey('users_columns_value.' . $column1->id, $result['column']);

        // 検証: 項目2はバリデーションに含まれない（非表示のため）
        $this->assertArrayNotHasKey('users_columns_value.' . $column2->id, $result['column']);
    }

    /**
     * isColumnDisplayed: トリガー項目が削除された場合のフォールバック動作
     *
     * @test
     */
    public function testIsColumnDisplayedWhenTriggerColumnIsDeleted()
    {
        // 項目セットを作成
        $columns_set = UsersColumnsSet::create([
            'name' => 'テスト項目セット',
            'display_sequence' => 1,
        ]);

        // トリガー項目を作成
        $trigger_column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::radio,
            'column_name' => 'トリガー項目',
            'required' => Required::off,
        ]);

        // 条件付き表示項目を作成
        $column = UsersColumns::create([
            'columns_set_id' => $columns_set->id,
            'column_type' => UserColumnType::text,
            'column_name' => 'テスト項目',
            'required' => Required::on,
            'conditional_display_flag' => ShowType::show,
            'conditional_trigger_column_id' => $trigger_column->id,
            'conditional_operator' => ConditionalOperator::equals,
            'conditional_value' => 'はい',
        ]);

        // トリガー項目を削除
        $trigger_column->delete();

        // リクエストデータ
        $request_data = [
            'users_columns_value' => [],
        ];

        // テスト実行
        $result = UsersTool::isColumnDisplayed($column, $request_data);

        // 検証: トリガーが削除されている場合は安全側（表示）に倒す
        $this->assertTrue($result);
    }
}
