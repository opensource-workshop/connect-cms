<?php

namespace Tests\Unit\Utilities\Network;

use App\Utilities\Network\IpAddressUtils;
use PHPUnit\Framework\TestCase;

/**
 * IPアドレス範囲判定（IPv4/IPv6/CIDR）のテスト
 *
 * ログイン制限・ページ・APIのIP制限で共通利用する判定と、設定値の形式チェックを検証する。
 */
class IpAddressUtilsTest extends TestCase
{
    /**
     * 範囲判定の期待値
     *
     * @dataProvider rangeProvider
     */
    public function testIsRange(string $remote, string $rule, bool $expected): void
    {
        $this->assertSame($expected, IpAddressUtils::isRange($remote, $rule));
    }

    public function rangeProvider(): array
    {
        return [
            '全許可' => ['1.2.3.4', '*', true],
            'IPv4完全一致' => ['192.168.1.10', '192.168.1.10', true],
            'IPv4不一致' => ['192.168.1.11', '192.168.1.10', false],
            'IPv4 /24内' => ['192.168.1.200', '192.168.1.0/24', true],
            'IPv4 /24外' => ['192.168.2.1', '192.168.1.0/24', false],
            'IPv4 /20 (8bit非境界)' => ['10.0.15.1', '10.0.0.0/20', true],
            'IPv4 /20外' => ['10.0.16.1', '10.0.0.0/20', false],
            'IPv4 /0 は全て' => ['8.8.8.8', '0.0.0.0/0', true],
            'IPv6完全一致(表記揺れ)' => ['1111:1111:1111:1111:aaaa:aaaa:aaaa:0001', '1111:1111:1111:1111:AAAA:aaaa:aaaa:1', true],
            'IPv6 /128' => ['1111:1111:1111:1111:aaaa:aaaa:aaaa:0001', '1111:1111:1111:1111:aaaa:aaaa:aaaa:0001/128', true],
            'IPv6 /64内' => ['1111:1111:1111:1111:aaaa:aaaa:aaaa:0001', '1111:1111:1111:1111::/64', true],
            'IPv6 /64内(0省略なし表記)' => ['1111:1111:1111:1111:aaaa:aaaa:aaaa:0001', '1111:1111:1111:1111:0:0:0:0/64', true],
            'IPv6 /64外' => ['1111:1111:1111:1112:aaaa:aaaa:aaaa:0001', '1111:1111:1111:1111::/64', false],
            'IPv6 短いマスク /16 は範囲外を拒否' => ['2222::1', '1111::/16', false],
            'IPv6 /32 範囲外' => ['1111:1112::1', '1111:1111::/32', false],
            'IPv6 /61 (ビット境界)' => ['1111:1111:1111:1118::1', '1111:1111:1111:1110::/61', false],
            'IPv6 /61 内' => ['1111:1111:1111:1117::1', '1111:1111:1111:1110::/61', true],
            'IPv4ルールにIPv6接続元' => ['1111::1', '192.168.1.0/24', false],
            'IPv6ルールにIPv4接続元' => ['192.168.1.1', '1111::/16', false],
            'マスク範囲超過(IPv4)' => ['192.168.1.1', '192.168.1.0/33', false],
            'マスク範囲超過(IPv6)' => ['1111::1', '1111::/129', false],
            'マスクが数字でない' => ['192.168.1.1', '192.168.1.0/abc', false],
            '不正なルール' => ['192.168.1.1', 'abc', false],
            '空ルール' => ['192.168.1.1', '', false],
            'IPv4射影アドレスはIPv4ルールと一致しない(対象外)' => ['::ffff:1.2.3.4', '1.2.3.4', false],
            'IPv4接続元はIPv4射影アドレスのルールと一致しない(対象外)' => ['1.2.3.4', '::ffff:1.2.3.4', false],
            '不正な接続元' => ['abc', '192.168.1.0/24', false],
        ];
    }

    /**
     * 設定値の形式チェックの期待値
     *
     * @dataProvider validRuleProvider
     */
    public function testIsValidRule(string $rule, bool $expected): void
    {
        $this->assertSame($expected, IpAddressUtils::isValidRule($rule));
    }

    public function validRuleProvider(): array
    {
        return [
            ['*', true],
            ['192.168.1.1', true],
            ['192.168.1.0/24', true],
            ['1111:1111:1111:1111::/64', true],
            ['::1', true],
            ['192.168.1.0/33', false],
            ['1111::/129', false],
            ['1111::/-1', false],
            ['192.168.1.*', false],
            ['192.168.1.0/24/8', false],
            ['', false],
            ['192.168.1.0/', false],
            ['/24', false],
            ['192.168.1.0/ 24', false],
            ['192.168.1.0/024', false],
            ['192.168.1.0/+24', false],
            ['::ffff:1.2.3.4', true],
        ];
    }
}
