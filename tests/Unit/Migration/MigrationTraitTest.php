<?php

namespace Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;

use App\Console\Commands\Migration\ExportNc2;

class MigrationTraitTest extends TestCase
{
    /**
     * privateメソッドのcleaningContentのテスト
     *
     * @return void
     */
    public function testCleaningContent()
    {
        $content = '<span style="font-size:12.8px;caret-color:rgb(0, 0, 0);color:rgb(0, 0, 0);">テスト</span>';
        // $content = '<span style="caret-color:rgb(0, 0, 0);color:rgb(0, 0, 0);">テスト</span>';
        $nc2_module_name = 'bbs';
        $migration_config['bbses']['export_clear_style'] = [
            0 => "font-family",
            1 => "font-size",
            2 => "caret-color",
            3 => "color",
        ];

        // テスト対象のクラスをnewする.
        $controller = new ExportNc2();
        // ReflectionClassをテスト対象のクラスをもとに作る.
        $reflection = new \ReflectionClass($controller);

        // privateメソッド取得
        $method = $reflection->getMethod('cleaningContent');
        // アクセス許可をする.
        $method->setAccessible(true);

        // プロパティの値を取得
        $property = $reflection->getProperty('migration_config');
        // privateプロパティのアクセス範囲を設定（trueを指定でアクセスできるようになる）
        $property->setAccessible(true);
        // 取得したプロパティをテスト用に上書き
        $property->setValue($controller, $migration_config);

        // メソッド実行
        $content = $method->invokeArgs($controller, [$content, $nc2_module_name]);

        // [debug]
        // var_dump($content);

        $this->assertEquals('<span>テスト</span>', $content, 'css属性除去できてない');
    }

    /**
     * privateメソッドのcleaningContentのパターンテスト
     *
     * @return void
     */
    public function testCleaningContentPattern()
    {
        $patterns = [
            'content別パターン' => [
                'content' => '<span style="caret-color:rgb(0, 0, 0);color:rgb(0, 0, 0);">テスト</span>',
                'migration_config' => ['bbses' => ['export_clear_style' => [
                    "font-family",
                    "font-size",
                    "caret-color",
                    "color",
                ]]],
                'nc2_module_name' => 'bbs',
            ],
            'migration_config順番違い' => [
                'content' => '<span style="font-size:12.8px;caret-color:rgb(0, 0, 0);color:rgb(0, 0, 0);">テスト</span>',
                'migration_config' => ['bbses' => ['export_clear_style' => [
                    "color",
                    "font-family",
                    "font-size",
                    "caret-color",
                ]]],
                'nc2_module_name' => 'bbs',
            ],
        ];

        foreach ($patterns as $key => $pattern) {
            // ReflectionClassをテスト対象のクラスをもとに作る.
            $controller = new ExportNc2();
            $reflection = new \ReflectionClass($controller);

            // privateメソッド取得＆アクセス許可
            $method = $reflection->getMethod('cleaningContent');
            $method->setAccessible(true);

            // privateプロパティの値をセット
            $property = $reflection->getProperty('migration_config');
            $property->setAccessible(true);
            $property->setValue($controller, $pattern['migration_config']);

            // メソッド実行
            $content = $method->invokeArgs($controller, [$pattern['content'], $pattern['nc2_module_name']]);

            $this->assertEquals('<span>テスト</span>', $content, "{$key} css属性除去できてない");
        }
    }
}
