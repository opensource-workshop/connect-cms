<?php

namespace App\Utilities\Migration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

use App\Utilities\Url\UrlUtils;

class MigrationHttpClientUtils
{
    /**
     * 移行処理用のHTTPクライアントを生成する
     *
     * @return Client
     */
    public static function createClient(array $runtime_options = []): Client
    {
        return new Client(self::buildClientOptions($runtime_options));
    }

    /**
     * connect設定から移行処理用のHTTPクライアントオプションを生成する
     * （主にテストでの検証用に公開）
     *
     * @return array
     */
    public static function buildClientOptions(array $runtime_options = []): array
    {
        $http_client_options = [
            'http_errors' => false,
            'allow_redirects' => false,
        ];

        $timeout = config('connect.CURL_TIMEOUT');
        if (!empty($timeout)) {
            $http_client_options['timeout'] = (float) $timeout;
        }

        if (self::shouldUseProxy($runtime_options)) {
            $proxy = self::buildProxyOption();
            if ($proxy !== null) {
                $http_client_options['proxy'] = $proxy;
            }
        }

        return $http_client_options;
    }

    /**
     * 文字列レスポンスとしてGETする
     *
     * @param Client $http_client
     * @param string $url
     * @return array
     */
    public static function get(Client $http_client, string $url, array $runtime_options = []): array
    {
        return self::request($http_client, 'GET', $url, [], $runtime_options);
    }

    /**
     * ファイルへ保存しながらGETする
     *
     * @param Client $http_client
     * @param string $url
     * @param string $sink_path
     * @return array
     */
    public static function downloadToFile(Client $http_client, string $url, string $sink_path, array $runtime_options = []): array
    {
        return self::request($http_client, 'GET', $url, ['sink' => $sink_path], $runtime_options);
    }

    /**
     * HTTPリクエストを実行する
     *
     * @param Client $http_client
     * @param string $method
     * @param string $url
     * @param array $request_options
     * @return array
     */
    private static function request(Client $http_client, string $method, string $url, array $request_options, array $runtime_options = []): array
    {
        $proxy_option = array_key_exists('proxy', $request_options)
            ? $request_options['proxy']
            : (self::shouldUseProxy($runtime_options) ? self::buildProxyOption() : null);
        $is_proxy_used = self::hasConfiguredProxy($proxy_option);

        // プロキシ未使用時のみ、事前検証で解決したホスト名を固定して TOCTOU を防ぐ。
        if (!$is_proxy_used) {
            $request_options = self::applyDnsPinning($url, $request_options);
        }

        $handler_stats = [];
        $request_options['on_stats'] = function ($stats) use (&$handler_stats) {
            if (is_object($stats) && method_exists($stats, 'getHandlerStats')) {
                $handler_stats = (array) $stats->getHandlerStats();
            }
        };

        try {
            $response = $http_client->request($method, $url, $request_options);
        } catch (GuzzleException $e) {
            $error_message = "HTTP [{$method}] {$url} : failed. " . $e->getMessage();
            Log::error($error_message);
            throw new \RuntimeException($error_message, 0, $e);
        }

        self::assertGlobalHandlerPrimaryIp($handler_stats, $url, $is_proxy_used);

        $body = '';
        if (!array_key_exists('sink', $request_options)) {
            $body = (string) $response->getBody();
        }

        return [
            'body' => $body,
            'http_code' => (int) $response->getStatusCode(),
            'location' => trim($response->getHeaderLine('Location')),
            'content_disposition' => self::formatContentDispositionHeader($response->getHeaderLine('Content-Disposition')),
        ];
    }

    /**
     * ランタイム指定を加味してプロキシを利用するかを判定する
     *
     * @param array $runtime_options
     * @return bool
     */
    private static function shouldUseProxy(array $runtime_options = []): bool
    {
        $proxy_tunnel_enabled = (bool) config('connect.HTTPPROXYTUNNEL');
        if (!$proxy_tunnel_enabled) {
            return false;
        }

        if (array_key_exists('use_proxy', $runtime_options)) {
            return (bool) $runtime_options['use_proxy'];
        }

        return true;
    }

    /**
     * プロキシ未使用時に cURL の名前解決を固定する（DNS pinning）
     *
     * @param string $url
     * @param array $request_options
     * @return array
     */
    private static function applyDnsPinning(string $url, array $request_options): array
    {
        $resolve_entries = self::buildCurlResolveEntries($url);
        if (empty($resolve_entries)) {
            return $request_options;
        }

        if (!defined('CURLOPT_RESOLVE')) {
            return $request_options;
        }

        $curl_options = [];
        if (isset($request_options['curl']) && is_array($request_options['curl'])) {
            $curl_options = $request_options['curl'];
        }

        $existing_resolve_entries = [];
        if (isset($curl_options[CURLOPT_RESOLVE]) && is_array($curl_options[CURLOPT_RESOLVE])) {
            $existing_resolve_entries = $curl_options[CURLOPT_RESOLVE];
        }

        $curl_options[CURLOPT_RESOLVE] = array_values(array_unique(array_merge($existing_resolve_entries, $resolve_entries)));
        $request_options['curl'] = $curl_options;

        return $request_options;
    }

    /**
     * URL から cURL CURLOPT_RESOLVE 用エントリを生成する
     *
     * @param string $url
     * @return array
     */
    private static function buildCurlResolveEntries(string $url): array
    {
        $parsed_url = parse_url($url);
        if ($parsed_url === false || !isset($parsed_url['scheme']) || !isset($parsed_url['host'])) {
            throw new \RuntimeException('[migrationHttpClient] Failed to parse URL for DNS pinning: ' . $url);
        }

        $host = self::normalizeHostForDnsPinning((string) $parsed_url['host']);
        if ($host === '') {
            throw new \RuntimeException('[migrationHttpClient] Empty host for DNS pinning: ' . $url);
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            if (!UrlUtils::isGlobalIp($host)) {
                throw new \RuntimeException('[migrationHttpClient] Rejected non-global host IP for DNS pinning: ' . $host . ' URL: ' . $url);
            }

            // IP直指定は再解決が発生しないため pinning 不要
            return [];
        }

        $resolved_ips = self::resolveHostIpsForDnsPinning($host);
        if (empty($resolved_ips)) {
            throw new \RuntimeException('[migrationHttpClient] Failed to resolve host for DNS pinning: ' . $host . ' URL: ' . $url);
        }

        $global_ips = [];
        foreach ($resolved_ips as $resolved_ip) {
            if (!UrlUtils::isGlobalIp($resolved_ip)) {
                throw new \RuntimeException('[migrationHttpClient] Rejected non-global DNS result for pinning: ' . $resolved_ip . ' URL: ' . $url);
            }

            $global_ips[] = $resolved_ip;
        }

        if (empty($global_ips)) {
            throw new \RuntimeException('[migrationHttpClient] Failed to collect global DNS results for pinning: ' . $host . ' URL: ' . $url);
        }

        $port = self::extractPortForDnsPinning($parsed_url);
        $pinned_addresses = array_map(function ($ip) {
            return self::formatIpForCurlResolve($ip);
        }, $global_ips);

        // IPv4/IPv6 のどちらか一方に固定しないよう、検証済みの全アドレスを pinning する。
        return [$host . ':' . $port . ':' . implode(',', $pinned_addresses)];
    }

    /**
     * DNS pinning 用にホストを正規化する
     *
     * @param string $host
     * @return string
     */
    private static function normalizeHostForDnsPinning(string $host): string
    {
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
     * DNS pinning 用にホスト名を解決する
     *
     * @param string $host
     * @return array
     */
    private static function resolveHostIpsForDnsPinning(string $host): array
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
     * DNS pinning 用に接続ポートを抽出する
     *
     * @param array $parsed_url
     * @return int
     */
    private static function extractPortForDnsPinning(array $parsed_url): int
    {
        if (isset($parsed_url['port'])) {
            return (int) $parsed_url['port'];
        }

        $scheme = strtolower((string) ($parsed_url['scheme'] ?? 'http'));
        return $scheme === 'https' ? 443 : 80;
    }

    /**
     * CURLOPT_RESOLVE 用に IP を整形する（IPv6 は [] で囲む）
     *
     * @param string $ip
     * @return string
     */
    private static function formatIpForCurlResolve(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return '[' . $ip . ']';
        }

        return $ip;
    }

    /**
     * Guzzle(cURL) の接続先IP(primary_ip)を再検証する
     *
     * @param array $handler_stats
     * @param string $url
     * @param bool $is_proxy_used
     * @return void
     */
    private static function assertGlobalHandlerPrimaryIp(array $handler_stats, string $url, bool $is_proxy_used): void
    {
        // プロキシ経由時のprimary_ipはプロキシIPになるため除外
        if (config('connect.HTTPPROXYTUNNEL') && $is_proxy_used) {
            return;
        }

        $primary_ip = trim((string) ($handler_stats['primary_ip'] ?? ''));
        if ($primary_ip === '') {
            $message = '[migrationHttpClient] Failed to verify primary_ip: ' . $url;
            Log::warning($message);
            throw new \RuntimeException($message);
        }

        $normalized_primary_ip = self::normalizePrimaryIpForGlobalCheck($primary_ip);
        if (!UrlUtils::isGlobalIp($normalized_primary_ip)) {
            $message = '[migrationHttpClient] Rejected non-global primary_ip: ' . $primary_ip . ' URL: ' . $url;
            Log::warning($message);
            throw new \RuntimeException($message);
        }
    }

    /**
     * proxy オプションが実質的に設定されているかを判定する
     *
     * @param mixed $proxy_option
     * @return bool
     */
    private static function hasConfiguredProxy($proxy_option): bool
    {
        if (is_string($proxy_option)) {
            return trim($proxy_option) !== '';
        }

        if (is_array($proxy_option)) {
            foreach ($proxy_option as $value) {
                if (is_string($value) && trim($value) !== '') {
                    return true;
                }
            }

            return false;
        }

        return !empty($proxy_option);
    }

    /**
     * libcurl の primary_ip 表現差分（IPv4-mapped IPv6）を正規化する
     *
     * @param string $ip
     * @return string
     */
    private static function normalizePrimaryIpForGlobalCheck(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            return $ip;
        }

        $binary_ip = @inet_pton($ip);
        if ($binary_ip === false || strlen($binary_ip) !== 16) {
            return $ip;
        }

        $ipv4_mapped_prefix = str_repeat("\x00", 10) . "\xff\xff";
        if (substr($binary_ip, 0, 12) !== $ipv4_mapped_prefix) {
            return $ip;
        }

        $ipv4 = @inet_ntop(substr($binary_ip, 12, 4));
        return is_string($ipv4) ? $ipv4 : $ip;
    }

    /**
     * connect設定からGuzzle用のプロキシURLを生成する
     *
     * @return string|null
     */
    private static function buildProxyOption(): ?string
    {
        $proxy = trim((string) config('connect.PROXY'));
        if ($proxy === '') {
            return null;
        }

        if (strpos($proxy, '://') === false) {
            $proxy = 'http://' . $proxy;
        }

        $proxy_parts = parse_url($proxy);
        if ($proxy_parts === false || !isset($proxy_parts['host'])) {
            return null;
        }

        $scheme = $proxy_parts['scheme'] ?? 'http';
        $host = $proxy_parts['host'];
        if (strpos($host, ':') !== false && strpos($host, '[') !== 0) {
            $host = '[' . $host . ']';
        }

        $port = $proxy_parts['port'] ?? null;
        $config_proxy_port = trim((string) config('connect.PROXYPORT'));
        if ($config_proxy_port !== '') {
            $port = $config_proxy_port;
        }

        $user = $proxy_parts['user'] ?? null;
        $pass = $proxy_parts['pass'] ?? null;
        $proxy_user_pwd = trim((string) config('connect.PROXYUSERPWD'));
        if ($proxy_user_pwd !== '') {
            list($user, $pass) = array_pad(explode(':', $proxy_user_pwd, 2), 2, '');
        }

        $auth = '';
        if (!empty($user)) {
            $auth = rawurlencode($user);
            if (!empty($pass)) {
                $auth .= ':' . rawurlencode($pass);
            }
            $auth .= '@';
        }

        $proxy_url = $scheme . '://' . $auth . $host;
        if (!empty($port)) {
            $proxy_url .= ':' . $port;
        }

        return $proxy_url;
    }

    /**
     * 既存cURLコールバック互換の形式でContent-Dispositionを返す
     *
     * @param string $header_value
     * @return string
     */
    private static function formatContentDispositionHeader(string $header_value): string
    {
        $header_value = trim($header_value);
        if ($header_value === '') {
            return '';
        }

        return 'Content-Disposition: ' . urldecode($header_value);
    }
}
