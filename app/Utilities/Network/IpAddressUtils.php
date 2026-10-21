<?php

namespace App\Utilities\Network;

/**
 * IPアドレス（IPv4/IPv6）の範囲判定ユーティリティ
 */
class IpAddressUtils
{
    /**
     * IPアドレスが範囲内か
     *
     * @param string $remote_ip 判定対象のIPアドレス
     * @param string $check_ips 許可/制限ルール。"*"、IPアドレス、CIDR形式（例: 192.168.1.0/24, 2001:db8::/64）
     * @return bool 範囲内ならtrue。不正な値は範囲外（false）として扱い、例外は投げない。
     */
    public static function isRange($remote_ip, $check_ips): bool
    {
        $check_ips = trim((string)$check_ips);

        // * は範囲内
        if ($check_ips === '*') {
            return true;
        }

        $parsed = self::parseRule($check_ips);
        if ($parsed === null) {
            return false;
        }
        list($check_bin, $prefix) = $parsed;

        $remote_bin = @inet_pton(trim((string)$remote_ip));
        // 不正なアドレス、またはIPv4とIPv6の組み合わせは範囲外
        if ($remote_bin === false || strlen($remote_bin) !== strlen($check_bin)) {
            return false;
        }

        // 先頭 $prefix ビットが一致するか
        $full_bytes = intdiv($prefix, 8);
        if (substr($remote_bin, 0, $full_bytes) !== substr($check_bin, 0, $full_bytes)) {
            return false;
        }
        $rest_bits = $prefix % 8;
        if ($rest_bits === 0) {
            return true;
        }
        $mask = (0xFF << (8 - $rest_bits)) & 0xFF;
        return (ord($remote_bin[$full_bytes]) & $mask) === (ord($check_bin[$full_bytes]) & $mask);
    }

    /**
     * 許可/制限ルールの形式が正しいか
     *
     * @param string $check_ips "*"、IPアドレス、CIDR形式のいずれか
     */
    public static function isValidRule($check_ips): bool
    {
        $check_ips = trim((string)$check_ips);
        return $check_ips === '*' || self::parseRule($check_ips) !== null;
    }

    /**
     * ルールをバイナリアドレスとプレフィックス長に分解する。不正ならnull。
     *
     * @return array|null [バイナリアドレス, プレフィックス長]
     */
    private static function parseRule(string $rule): ?array
    {
        $parts = explode('/', $rule);
        if (count($parts) > 2) {
            return null;
        }

        $bin = @inet_pton(trim($parts[0]));
        if ($bin === false) {
            return null;
        }
        $max_prefix = strlen($bin) * 8;

        // マスクなしは全ビット一致（単一アドレス）
        if (count($parts) === 1) {
            return [$bin, $max_prefix];
        }

        $mask = trim($parts[1]);
        if (!ctype_digit($mask) || (int)$mask > $max_prefix) {
            return null;
        }
        return [$bin, (int)$mask];
    }
}
