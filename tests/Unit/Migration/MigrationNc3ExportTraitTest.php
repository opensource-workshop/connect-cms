<?php

namespace Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;
use App\Console\Commands\Migration\ExportNc3;

class MigrationNc3ExportTraitTest extends TestCase
{
    /**
     * @var ExportNc3
     */
    private $controller;

    /**
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * テストメソッド実行前の共通処理
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ExportNc3();
        $this->reflection = new \ReflectionClass($this->controller);
    }

    /**
     * privateメソッドを取得してアクセス可能にする
     *
     * @param string $method_name
     * @return \ReflectionMethod
     */
    private function getPrivateMethod(string $method_name): \ReflectionMethod
    {
        $method = $this->reflection->getMethod($method_name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * privateプロパティを取得してアクセス可能にする
     *
     * @param string $property_name
     * @return \ReflectionProperty
     */
    private function getPrivateProperty(string $property_name): \ReflectionProperty
    {
        $property = $this->reflection->getProperty($property_name);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * 新しいコントローラインスタンスを作成（パターンテスト用）
     *
     * @return array [controller, reflection]
     */
    private function createNewController(): array
    {
        $controller = new ExportNc3();
        $reflection = new \ReflectionClass($controller);
        return [$controller, $reflection];
    }
    /**
     * privateメソッドのzeroSuppressのテスト
     *
     * @return void
     */
    public function testZeroSuppress()
    {
        $method = $this->getPrivateMethod('zeroSuppress');

        // テストケース1: 通常のケース（デフォルト4桁）
        $result = $method->invokeArgs($this->controller, [123]);
        $this->assertEquals('0123', $result, '4桁ゼロサプレスが正しく動作していない');

        // テストケース2: 桁数指定（6桁）
        $result = $method->invokeArgs($this->controller, [123, 6]);
        $this->assertEquals('000123', $result, '6桁ゼロサプレスが正しく動作していない');

        // テストケース3: 桁数を超える場合
        $result = $method->invokeArgs($this->controller, [12345, 4]);
        $this->assertEquals('12345', $result, '桁数を超える場合の処理が正しく動作していない');

        // テストケース4: 0の場合
        $result = $method->invokeArgs($this->controller, [0]);
        $this->assertEquals('0000', $result, '0の場合の処理が正しく動作していない');
    }

    /**
     * privateメソッドのzeroSuppressのパターンテスト
     *
     * @return void
     */
    public function testZeroSuppressPattern()
    {
        $patterns = [
            '2桁指定' => [
                'id' => 5,
                'size' => 2,
                'expected' => '05',
            ],
            '5桁指定' => [
                'id' => 42,
                'size' => 5,
                'expected' => '00042',
            ],
            '桁数と同じ' => [
                'id' => 1234,
                'size' => 4,
                'expected' => '1234',
            ],
            '桁数超過' => [
                'id' => 99999,
                'size' => 3,
                'expected' => '99999',
            ],
            '負の数' => [
                'id' => -123,
                'size' => 4,
                'expected' => '-123',
            ],
        ];

        foreach ($patterns as $key => $pattern) {
            [$controller, $reflection] = $this->createNewController();
            $method = $reflection->getMethod('zeroSuppress');
            $method->setAccessible(true);

            // メソッド実行
            $result = $method->invokeArgs($controller, [$pattern['id'], $pattern['size']]);

            $this->assertEquals($pattern['expected'], $result, "{$key} ゼロサプレス処理が正しく動作していない");
        }
    }

    /**
     * privateメソッドのgetImportPathのテスト
     *
     * @return void
     */
    public function testGetImportPath()
    {
        $method = $this->getPrivateMethod('getImportPath');

        // プライベートプロパティをセット
        $migration_base_property = $this->getPrivateProperty('migration_base');
        $migration_base_property->setValue($this->controller, '/var/migrations/');

        $import_base_property = $this->getPrivateProperty('import_base');
        $import_base_property->setValue($this->controller, 'nc3_export/');

        // テストケース1: import_baseを指定しない場合
        $result = $method->invokeArgs($this->controller, ['users.csv']);
        $this->assertEquals('/var/migrations/nc3_export/users.csv', $result, 'import_base未指定の場合のパス生成が正しく動作していない');

        // テストケース2: import_baseを指定する場合
        $result = $method->invokeArgs($this->controller, ['pages.csv', 'custom_export/']);
        $this->assertEquals('/var/migrations/custom_export/pages.csv', $result, 'import_base指定の場合のパス生成が正しく動作していない');
    }

    /**
     * privateメソッドのgetImportPathのパターンテスト
     *
     * @return void
     */
    public function testGetImportPathPattern()
    {
        $patterns = [
            'CSVファイル' => [
                'target' => 'test.csv',
                'import_base' => null,
                'migration_base' => '/app/migrations/',
                'default_import_base' => 'export/',
                'expected' => '/app/migrations/export/test.csv',
            ],
            'YAMLファイル' => [
                'target' => 'config.yml',
                'import_base' => 'yaml_export/',
                'migration_base' => '/data/',
                'default_import_base' => 'default/',
                'expected' => '/data/yaml_export/config.yml',
            ],
            'サブディレクトリ' => [
                'target' => 'plugins/blog.csv',
                'import_base' => null,
                'migration_base' => '/home/migration/',
                'default_import_base' => 'nc3/',
                'expected' => '/home/migration/nc3/plugins/blog.csv',
            ],
        ];

        foreach ($patterns as $key => $pattern) {
            [$controller, $reflection] = $this->createNewController();
            $method = $reflection->getMethod('getImportPath');
            $method->setAccessible(true);

            // プライベートプロパティをセット
            $migration_base_property = $reflection->getProperty('migration_base');
            $migration_base_property->setAccessible(true);
            $migration_base_property->setValue($controller, $pattern['migration_base']);

            $import_base_property = $reflection->getProperty('import_base');
            $import_base_property->setAccessible(true);
            $import_base_property->setValue($controller, $pattern['default_import_base']);

            // メソッド実行
            $result = $method->invokeArgs($controller, [$pattern['target'], $pattern['import_base']]);

            $this->assertEquals($pattern['expected'], $result, "{$key} パス生成が正しく動作していない");
        }
    }

    /**
     * privateメソッドのisTargetのテスト
     *
     * @return void
     */
    public function testIsTarget()
    {
        $method = $this->getPrivateMethod('isTarget');

        // プライベートプロパティをセット
        $target_property = $this->getPrivateProperty('target');
        $target_plugin_property = $this->getPrivateProperty('target_plugin');
        $migration_config_property = $this->getPrivateProperty('migration_config');

        // テストケース1: target='all'の場合
        $target_property->setValue($this->controller, 'all');
        $migration_config_property->setValue($this->controller, ['users' => ['export_users' => true]]);
        $result = $method->invokeArgs($this->controller, ['export', 'users']);
        $this->assertTrue($result, 'target=allの場合にtrueが返されない');

        // テストケース2: 指定したtargetと一致する場合
        $target_property->setValue($this->controller, 'pages');
        $migration_config_property->setValue($this->controller, ['pages' => ['export_pages' => true]]);
        $result = $method->invokeArgs($this->controller, ['export', 'pages']);
        $this->assertTrue($result, '指定したtargetと一致する場合にtrueが返されない');

        // テストケース3: 指定したtargetと一致しない場合
        $target_property->setValue($this->controller, 'users');
        $result = $method->invokeArgs($this->controller, ['export', 'pages']);
        $this->assertFalse($result, '指定したtargetと一致しない場合にfalseが返されない');
    }

    /**
     * privateメソッドのisTargetのパターンテスト
     *
     * @return void
     */
    public function testIsTargetPattern()
    {
        $patterns = [
            'all指定で対象' => [
                'target' => 'all',
                'target_plugin' => null,
                'command' => 'export',
                'check_target' => 'users',
                'check_target_plugin' => null,
                'migration_config' => ['users' => ['export_users' => true]],
                'expected' => true,
            ],
            '完全一致で対象' => [
                'target' => 'pages',
                'target_plugin' => null,
                'command' => 'export',
                'check_target' => 'pages',
                'check_target_plugin' => null,
                'migration_config' => ['pages' => ['export_pages' => true]],
                'expected' => true,
            ],
            '対象外' => [
                'target' => 'users',
                'target_plugin' => null,
                'command' => 'export',
                'check_target' => 'pages',
                'check_target_plugin' => null,
                'migration_config' => ['pages' => ['export_pages' => true]],
                'expected' => false,
            ],
            'プラグイン指定で対象' => [
                'target' => 'plugins',
                'target_plugin' => 'blog',
                'command' => 'export',
                'check_target' => 'plugins',
                'check_target_plugin' => 'blog',
                'migration_config' => ['plugins' => ['export_plugins' => ['blog']]],
                'expected' => true,
            ],
            'プラグイン全て指定' => [
                'target' => 'plugins',
                'target_plugin' => 'all',
                'command' => 'export',
                'check_target' => 'plugins',
                'check_target_plugin' => 'bbs',
                'migration_config' => ['plugins' => ['export_plugins' => ['bbs']]],
                'expected' => true,
            ],
        ];

        foreach ($patterns as $key => $pattern) {
            [$controller, $reflection] = $this->createNewController();
            $method = $reflection->getMethod('isTarget');
            $method->setAccessible(true);

            // プライベートプロパティをセット
            $target_property = $reflection->getProperty('target');
            $target_property->setAccessible(true);
            $target_property->setValue($controller, $pattern['target']);

            $target_plugin_property = $reflection->getProperty('target_plugin');
            $target_plugin_property->setAccessible(true);
            $target_plugin_property->setValue($controller, $pattern['target_plugin']);

            $migration_config_property = $reflection->getProperty('migration_config');
            $migration_config_property->setAccessible(true);
            $migration_config_property->setValue($controller, $pattern['migration_config']);

            // メソッド実行
            $result = $method->invokeArgs($controller, [$pattern['command'], $pattern['check_target'], $pattern['check_target_plugin']]);

            $this->assertEquals($pattern['expected'], $result, "{$key} 処理対象判定が正しく動作していない");
        }
    }

    /**
     * privateメソッドのcheckLangDirnameJpnのテスト
     *
     * @return void
     */
    public function testCheckLangDirnameJpn()
    {
        $method = $this->getPrivateMethod('checkLangDirnameJpn');

        // テストケース1: 日本語ID（2）の場合
        $result = $method->invokeArgs($this->controller, [2]);
        $this->assertTrue($result, '日本語IDの場合にtrueが返されない');

        // テストケース2: 英語ID（1）の場合
        $result = $method->invokeArgs($this->controller, [1]);
        $this->assertFalse($result, '英語IDの場合にfalseが返されない');

        // テストケース3: その他のID（3）の場合
        $result = $method->invokeArgs($this->controller, [3]);
        $this->assertFalse($result, 'その他のIDの場合にfalseが返されない');

        // テストケース4: 0の場合
        $result = $method->invokeArgs($this->controller, [0]);
        $this->assertFalse($result, '0の場合にfalseが返されない');

        // テストケース5: nullの場合
        $result = $method->invokeArgs($this->controller, [null]);
        $this->assertFalse($result, 'nullの場合にfalseが返されない');
    }

    /**
     * privateメソッドのgetCCDatetimeのテスト
     *
     * @return void
     */
    public function testGetCCDatetime()
    {
        $method = $this->getPrivateMethod('getCCDatetime');

        // テストケース1: 正常な日時文字列の場合
        $result = $method->invokeArgs($this->controller, ['2023-01-01 00:00:00']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result, '正常な日時文字列でCarbonインスタンスが返されない');
        $this->assertEquals('2023-01-01 09:00:00', $result->format('Y-m-d H:i:s'), '9時間加算されていない');

        // テストケース2: nullの場合
        $result = $method->invokeArgs($this->controller, [null]);
        $this->assertNull($result, 'nullの場合にnullが返されない');

        // テストケース3: 空文字の場合
        $result = $method->invokeArgs($this->controller, ['']);
        $this->assertNull($result, '空文字の場合にnullが返されない');

        // テストケース4: "0000-00-00 00:00:00"の場合
        $result = $method->invokeArgs($this->controller, ['0000-00-00 00:00:00']);
        $this->assertNull($result, 'ダミー日時の場合にnullが返されない');

        // テストケース5: Carbonインスタンスを渡した場合
        $carbon = new \Carbon\Carbon('2023-06-15 12:30:45');
        $result = $method->invokeArgs($this->controller, [$carbon]);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result, 'Carbonインスタンスを渡した場合にCarbonインスタンスが返されない');
        $this->assertEquals('2023-06-15 21:30:45', $result->format('Y-m-d H:i:s'), 'Carbonインスタンスで9時間加算されていない');
    }

    /**
     * privateメソッドのisOmmitのテスト
     *
     * @return void
     */
    public function testIsOmmit()
    {
        $method = $this->getPrivateMethod('isOmmit');

        // migration_configプロパティを直接設定
        $migration_config_property = $this->getPrivateProperty('migration_config');

        // テストケース1: 対象外設定に含まれる場合
        $migration_config_property->setValue($this->controller, [
            'blogs' => [
                'ommit_block_ids' => [1, 2, 3]
            ]
        ]);
        $result = $method->invokeArgs($this->controller, ['blogs', 'ommit_block_ids', 2]);
        $this->assertTrue($result, '対象外設定に含まれる場合にtrueが返されない');

        // テストケース2: 対象外設定に含まれない場合
        $migration_config_property->setValue($this->controller, [
            'blogs' => [
                'ommit_block_ids' => [1, 2, 3]
            ]
        ]);
        $result = $method->invokeArgs($this->controller, ['blogs', 'ommit_block_ids', 5]);
        $this->assertFalse($result, '対象外設定に含まれない場合にfalseが返されない');

        // テストケース3: 設定が空の場合
        $migration_config_property->setValue($this->controller, [
            'blogs' => [
                'ommit_block_ids' => []
            ]
        ]);
        $result = $method->invokeArgs($this->controller, ['blogs', 'ommit_block_ids', 1]);
        $this->assertFalse($result, '設定が空の場合にfalseが返されない');

        // テストケース4: セクションが存在しない場合
        $migration_config_property->setValue($this->controller, []);
        $result = $method->invokeArgs($this->controller, ['blogs', 'ommit_block_ids', 1]);
        $this->assertFalse($result, 'セクションが存在しない場合にfalseが返されない');

        // テストケース5: キーが存在しない場合
        $migration_config_property->setValue($this->controller, [
            'blogs' => []
        ]);
        $result = $method->invokeArgs($this->controller, ['blogs', 'ommit_block_ids', 1]);
        $this->assertFalse($result, 'キーが存在しない場合にfalseが返されない');
    }
}
