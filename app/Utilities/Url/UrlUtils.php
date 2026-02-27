<?php

namespace App\Utilities\Url;

class UrlUtils
{
    /**
     * Check if URL is http/https and resolves only to global (non private/reserved) IPs.
     */
    public static function isGlobalHttpUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        $parsed_url = parse_url($url);
        if ($parsed_url === false || !isset($parsed_url['scheme']) || !isset($parsed_url['host'])) {
            return false;
        }

        $scheme = strtolower($parsed_url['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = self::normalizeHost($parsed_url['host']);
        if ($host === '' || self::isLocalhost($host)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return self::isGlobalIp($host);
        }

        $resolved_ips = self::resolveHostIps($host);
        if (empty($resolved_ips)) {
            return false;
        }

        foreach ($resolved_ips as $ip) {
            if (!self::isGlobalIp($ip)) {
                return false;
            }
        }

        return true;
    }

    /**
     * URLホストを正規化する（IPv6[]除去、末尾ドット除去、IDN変換）
     *
     * @param string $host
     * @return string
     */
    private static function normalizeHost(string $host): string
    {
        // IPv6 literal host is returned as "[::1]" by parse_url.
        if (preg_match('/^\[(.*)\]$/', $host, $matches)) {
            $host = $matches[1];
        }

        $host = rtrim(strtolower($host), '.');
        if ($host === '') {
            return '';
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $host;
        }

        if (function_exists('idn_to_ascii')) {
            $idn_flags = defined('IDNA_DEFAULT') ? IDNA_DEFAULT : 0;
            $idn_variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
            $ascii_host = @idn_to_ascii($host, $idn_flags, $idn_variant);
            if ($ascii_host === false || $ascii_host === '') {
                return '';
            }
            $host = strtolower($ascii_host);
        }

        return $host;
    }

    /**
     * localhost または *.localhost かどうかを判定する
     *
     * @param string $host
     * @return bool
     */
    private static function isLocalhost(string $host): bool
    {
        return $host === 'localhost' || preg_match('/\.localhost$/', $host) === 1;
    }

    /**
     * ホスト名をDNS解決し、取得できたIP一覧を返す
     *
     * @param string $host
     * @return array
     */
    private static function resolveHostIps(string $host): array
    {
        $ips = [];

        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A + DNS_AAAA);
            if (is_array($records)) {
                foreach ($records as $record) {
                    if (isset($record['ip'])) {
                        $ips[] = $record['ip'];
                    }
                    if (isset($record['ipv6'])) {
                        $ips[] = $record['ipv6'];
                    }
                }
            }
        }

        if (empty($ips)) {
            $v4_addresses = @gethostbynamel($host);
            if (is_array($v4_addresses)) {
                $ips = array_merge($ips, $v4_addresses);
            }
        }

        $ips = array_filter(array_unique($ips), function ($ip) {
            return filter_var($ip, FILTER_VALIDATE_IP) !== false;
        });

        return array_values($ips);
    }

    /**
     * グローバル到達可能なIPかを判定する
     *
     * @param string $ip
     * @return bool
     */
    public static function isGlobalIp(string $ip): bool
    {
        if (self::isIpv4MappedIpv6($ip)) {
            return false;
        }

        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
    }

    /**
     * IPv4-mapped IPv6 (::ffff:x.x.x.x) かどうかを判定する
     *
     * @param string $ip
     * @return bool
     */
    private static function isIpv4MappedIpv6(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            return false;
        }

        $binary_ip = @inet_pton($ip);
        if ($binary_ip === false || strlen($binary_ip) !== 16) {
            return false;
        }

        return substr($binary_ip, 0, 12) === str_repeat("\x00", 10) . "\xff\xff";
    }
}
