<?php

namespace Tests\Unit\Rules;

use App\Rules\CustomValiIpAddressRange;
use PHPUnit\Framework\TestCase;

/**
 * IPアドレス制限の指定形式チェック（CustomValiIpAddressRange）のテスト
 *
 * ログイン制限・API（single）とページのIPアドレス制限（multiple）で、
 * 許可する指定形式の違いと、不正な入力でも例外にならないことを検証する。
 */
class CustomValiIpAddressRangeTest extends TestCase
{
    /**
     * 1件指定: IPv4/IPv6/CIDR/"*"は許可、複数指定は不可、空は他ルールに任せる
     *
     * @dataProvider singleProvider
     */
    public function testSingle($value, bool $expected): void
    {
        $this->assertSame($expected, CustomValiIpAddressRange::single()->passes('ip', $value));
    }

    public function singleProvider(): array
    {
        return [
            'IPv4' => ['192.168.1.1', true],
            'IPv4 CIDR' => ['192.168.1.0/24', true],
            'IPv6 CIDR' => ['1111::/64', true],
            '全許可' => ['*', true],
            '空文字' => ['', true],
            'null' => [null, true],
            '前後の空白' => [' 192.168.1.1 ', true],
            'カンマ区切りは不可' => ['192.168.1.1, 10.0.0.1', false],
            'スラッシュのみ' => ['192.168.1.0/', false],
            'マスクのみ' => ['/24', false],
            'マスク前の空白' => ['192.168.1.0/ 24', false],
            'マスクの先頭0' => ['192.168.1.0/024', false],
            'マスク範囲超過' => ['192.168.1.0/33', false],
            'ワイルドカード風' => ['192.168.1.*', false],
            '配列' => [['192.168.1.1'], false],
            '数値型' => [123, false],
        ];
    }

    /**
     * 複数指定: カンマ区切りはスペースを許容し、1件でも不正ならエラー。
     * "*"は単独でも他の指定と併記してもエラー（ページのIPアドレス制限の仕様）。
     *
     * @dataProvider multipleProvider
     */
    public function testMultiple($value, bool $expected): void
    {
        $this->assertSame($expected, CustomValiIpAddressRange::multiple()->passes('ip', $value));
    }

    public function multipleProvider(): array
    {
        return [
            '複数(空白あり・なし混在)' => ['192.168.1.1, 10.0.0.0/8,2001:db8::/32', true],
            '1件' => ['192.168.1.1', true],
            '空文字' => ['', true],
            'null' => [null, true],
            '1件が不正' => ['192.168.1.1,abc', false],
            '末尾カンマ' => ['192.168.1.1,', false],
            '先頭カンマ' => [',192.168.1.1', false],
            '連続カンマ' => ['192.168.1.1,,10.0.0.1', false],
            '全許可は不可' => ['*', false],
            '全許可の併記も不可' => ['192.168.1.1, *', false],
            '配列' => [['192.168.1.1'], false],
            '数値型' => [123, false],
        ];
    }
}
