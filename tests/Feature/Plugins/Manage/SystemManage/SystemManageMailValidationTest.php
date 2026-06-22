<?php

namespace Tests\Feature\Plugins\Manage\SystemManage;

use App\Models\Core\UsersRoles;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * システム管理のメール設定更新で、.env に書き込む値の入力検証を確認する。
 *
 * HTTP 経路から検証し、制御文字による .env の行追加や設定破損を防ぐことを守る。
 * 管理プラグインの動的ロードが同一プロセス内の複数リクエストでクラス再宣言になるため、
 * separate process が必要。dataProvider はケースごとに別プロセスと DB 初期化が走るため使わない。
 * require_once 等でロード方式を見直した後は、dataProvider に戻せる可能性がある。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SystemManageMailValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト開始前の .env 内容。
     *
     * @var string|null
     */
    private $original_env_content;

    /**
     * テスト開始前に .env が存在したか。
     *
     * @var bool
     */
    private $original_env_exists;

    /**
     * テスト前に .env の状態を退避する。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $env_path = base_path('.env');
        $this->original_env_exists = file_exists($env_path);
        $this->original_env_content = $this->original_env_exists ? file_get_contents($env_path) : null;
    }

    /**
     * テストで .env を書き換えた場合でも元の状態へ戻す。
     */
    protected function tearDown(): void
    {
        $env_path = base_path('.env');

        if ($this->original_env_exists) {
            file_put_contents($env_path, $this->original_env_content);
        } elseif (file_exists($env_path)) {
            unlink($env_path);
        }

        parent::tearDown();
    }

    /**
     * メール設定の1行項目では、実改行による .env の別行追加を許さないこと。
     */
    public function testMailSettingsRejectControlCharacters(): void
    {
        $admin = $this->createSystemAdminUser();

        foreach ($this->controlCharacterPayloads() as $payload) {
            $before_env = $this->readEnvFile();

            $response = $this->actingAs($admin)->post('/manage/system/updateMail', $this->buildMailPayload([
                $payload['field'] => $payload['value'],
            ]));

            $response->assertRedirect('/manage/system/mail');
            $response->assertSessionHasErrors([$payload['field']]);
            $this->assertSame($before_env, $this->readEnvFile());
        }
    }

    /**
     * SMTPポート番号は数値かつポート範囲内だけを受け付けること。
     */
    public function testMailPortRejectsInvalidValues(): void
    {
        $admin = $this->createSystemAdminUser();

        foreach ($this->invalidPorts() as $mail_port) {
            $before_env = $this->readEnvFile();

            $response = $this->actingAs($admin)->post('/manage/system/updateMail', $this->buildMailPayload([
                'mail_port' => $mail_port,
            ]));

            $response->assertRedirect('/manage/system/mail');
            $response->assertSessionHasErrors(['mail_port']);
            $this->assertSame($before_env, $this->readEnvFile());
        }
    }

    /**
     * メール暗号化方式は画面で選べる値以外を受け付けないこと。
     */
    public function testMailEncryptionRejectsUnexpectedValue(): void
    {
        $admin = $this->createSystemAdminUser();
        $before_env = $this->readEnvFile();

        $response = $this->actingAs($admin)->post('/manage/system/updateMail', $this->buildMailPayload([
            'mail_encryption' => "tls\nAPP_DEBUG=true",
        ]));

        $response->assertRedirect('/manage/system/mail');
        $response->assertSessionHasErrors(['mail_encryption']);
        $this->assertSame($before_env, $this->readEnvFile());
    }

    /**
     * 正常なメール設定値は従来通り .env に保存できること。
     */
    public function testMailSettingsAcceptValidValues(): void
    {
        $admin = $this->createSystemAdminUser();
        $this->writeTestEnvFile();
        $this->setMailConfigValuesForTestEnv();

        $response = $this->actingAs($admin)->post('/manage/system/updateMail', $this->buildMailPayload([
            'mail_from_address' => 'new@example.com',
            'mail_from_name' => 'New Mail Name',
            'mail_host' => 'new.smtp.example.com',
            'mail_port' => '2525',
            'mail_username' => 'new-user',
            'mail_password' => 'new-pass',
            'mail_encryption' => 'ssl',
        ]));

        $response->assertRedirect('/manage/system/mail');
        $response->assertSessionDoesntHaveErrors();

        $env_content = $this->readEnvFile();
        $this->assertStringContainsString('MAIL_FROM_ADDRESS=new@example.com', $env_content);
        $this->assertStringContainsString('MAIL_FROM_NAME="New Mail Name"', $env_content);
        $this->assertStringContainsString('MAIL_HOST=new.smtp.example.com', $env_content);
        $this->assertStringContainsString('MAIL_PORT=2525', $env_content);
        $this->assertStringContainsString('MAIL_USERNAME=new-user', $env_content);
        $this->assertStringContainsString('MAIL_PASSWORD=new-pass', $env_content);
        $this->assertStringContainsString('MAIL_ENCRYPTION=ssl', $env_content);
    }

    /**
     * 既存のメール設定値に正規表現の特殊文字が含まれても、次回更新で .env を破損しないこと。
     */
    public function testMailSettingsPreserveEnvWhenCurrentPasswordContainsRegexDelimiter(): void
    {
        $admin = $this->createSystemAdminUser();
        $this->writeTestEnvFile([
            'MAIL_PASSWORD' => 'old/pass',
        ]);
        $this->setMailConfigValuesForTestEnv([
            'mail.password' => 'old/pass',
        ]);

        $response = $this->actingAs($admin)->post('/manage/system/updateMail', $this->buildMailPayload([
            'mail_password' => 'new-pass',
        ]));

        $response->assertRedirect('/manage/system/mail');
        $response->assertSessionDoesntHaveErrors();

        $env_content = $this->readEnvFile();
        $this->assertStringContainsString('APP_NAME=Connect-CMS-Test', $env_content);
        $this->assertStringContainsString('MAIL_PASSWORD=new-pass', $env_content);
    }

    /**
     * 制御文字を含む入力の代表例を返す。
     *
     * @return array<int, array{field: string, value: string}>
     */
    private function controlCharacterPayloads(): array
    {
        return [
            ['field' => 'mail_from_address', 'value' => "test@example.com\nAPP_DEBUG=true"],
            ['field' => 'mail_from_name', 'value' => "Mail Name\rAPP_DEBUG=true"],
            ['field' => 'mail_host', 'value' => "smtp\t.example.com"],
            ['field' => 'mail_username', 'value' => 'user' . chr(0) . 'name'],
            ['field' => 'mail_password', 'value' => 'pass' . chr(27) . 'word'],
            ['field' => 'mail_password', 'value' => 'pass' . chr(127) . 'word'],
        ];
    }

    /**
     * SMTPポート番号として拒否すべき代表例を返す。
     *
     * @return array<int, string>
     */
    private function invalidPorts(): array
    {
        return ['smtp', '0', '65536'];
    }

    /**
     * システム管理者権限を持つユーザーを作成する。
     */
    private function createSystemAdminUser(): User
    {
        $user = User::factory()->create();

        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'manage',
            'role_name' => 'admin_system',
            'role_value' => 1,
        ]);

        return $user;
    }

    /**
     * メール設定更新に必要な標準入力値を組み立てる。
     */
    private function buildMailPayload(array $overrides = []): array
    {
        return array_merge([
            'mail_from_address' => 'test@example.com',
            'mail_from_name' => 'Test Mail',
            'mail_host' => 'smtp.example.com',
            'mail_port' => '587',
            'mail_username' => 'user',
            'mail_password' => 'pass',
            'mail_encryption' => 'tls',
        ], $overrides);
    }

    /**
     * .env の現在内容を取得する。
     */
    private function readEnvFile(): ?string
    {
        $env_path = base_path('.env');

        return file_exists($env_path) ? file_get_contents($env_path) : null;
    }

    /**
     * 正常系保存の検証用に、置換対象が明確な .env を用意する。
     */
    private function writeTestEnvFile(array $overrides = []): void
    {
        $values = array_merge([
            'APP_NAME' => 'Connect-CMS-Test',
            'MAIL_FROM_ADDRESS' => 'old@example.com',
            'MAIL_FROM_NAME' => '"Old Mail Name"',
            'MAIL_HOST' => 'old.smtp.example.com',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => 'old-user',
            'MAIL_PASSWORD' => 'old-pass',
            'MAIL_ENCRYPTION' => 'tls',
        ], $overrides);

        file_put_contents(base_path('.env'), implode("\n", [
            'APP_NAME=' . $values['APP_NAME'],
            'MAIL_FROM_ADDRESS=' . $values['MAIL_FROM_ADDRESS'],
            'MAIL_FROM_NAME=' . $values['MAIL_FROM_NAME'],
            'MAIL_HOST=' . $values['MAIL_HOST'],
            'MAIL_PORT=' . $values['MAIL_PORT'],
            'MAIL_USERNAME=' . $values['MAIL_USERNAME'],
            'MAIL_PASSWORD=' . $values['MAIL_PASSWORD'],
            'MAIL_ENCRYPTION=' . $values['MAIL_ENCRYPTION'],
            '',
        ]));
    }

    /**
     * updateMail() の既存値検索がテスト用 .env と一致するよう設定する。
     */
    private function setMailConfigValuesForTestEnv(array $overrides = []): void
    {
        config(array_merge([
            'mail.from.address' => 'old@example.com',
            'mail.from.name' => 'Old Mail Name',
            'mail.host' => 'old.smtp.example.com',
            'mail.port' => '587',
            'mail.username' => 'old-user',
            'mail.password' => 'old-pass',
            'mail.encryption' => 'tls',
        ], $overrides));
    }
}
