<?php

namespace Tests\Unit\Utilities\Url;

use App\Utilities\Url\UrlUtils;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * UrlUtils の URL 判定・正規化を検証するテスト。
 *
 * テスト方針:
 * - 外部通信や実 DNS に依存しない入力は戻り値そのもので検証する。
 * - リダイレクト先の正規化は、利用者が踏む最終 Location が外部サイトにならないことを主眼に検証する。
 */
class UrlUtilsTest extends TestCase
{
    /**
     * テストの意図:
     * 外部取得対象 URL は http/https かつグローバル到達可能な宛先だけを許可する。
     *
     * @dataProvider globalHttpUrlProvider
     */
    public function testIsGlobalHttpUrl(string $url, bool $expected)
    {
        $this->assertSame($expected, UrlUtils::isGlobalHttpUrl($url), $url);
    }

    public function globalHttpUrlProvider(): array
    {
        return [
            'allow_global_ipv4_http' => ['http://8.8.8.8', true],
            'allow_global_ipv4_https' => ['https://1.1.1.1/path', true],
            'allow_global_domain' => ['https://example.com', true],
            'deny_non_http_scheme' => ['ftp://8.8.8.8', false],
            'deny_localhost' => ['http://localhost', false],
            'deny_localhost_subdomain' => ['http://foo.localhost', false],
            'deny_localhost_trailing_dot' => ['http://localhost./', false],
            'deny_loopback_ipv4' => ['http://127.0.0.1', false],
            'deny_private_ipv4' => ['http://10.0.0.1', false],
            'deny_private_172_ipv4' => ['http://172.16.0.1', false],
            'deny_private_192_ipv4' => ['http://192.168.1.1', false],
            'deny_unspecified_ipv4' => ['http://0.0.0.0', false],
            'deny_link_local_ipv4' => ['http://169.254.169.254/latest/meta-data', false],
            'deny_decimal_ipv4_notation' => ['http://2130706433', false],
            'deny_octal_ipv4_notation' => ['http://0177.0.0.1', false],
            'deny_loopback_ipv6' => ['http://[::1]/', false],
            'deny_private_ipv6_ula' => ['http://[fd00::1]/', false],
            'deny_ipv4_mapped_loopback_ipv6' => ['http://[::ffff:127.0.0.1]/', false],
            'deny_ipv4_mapped_link_local_ipv6' => ['http://[::ffff:169.254.169.254]/', false],
            'deny_ipv4_mapped_expanded_ipv6' => ['http://[0:0:0:0:0:ffff:7f00:1]/', false],
            'deny_empty_string' => ['', false],
            'deny_scheme_only' => ['http://', false],
            'deny_protocol_relative_url' => ['//example.com', false],
            'deny_data_scheme' => ['data:text/html,<h1>Hi</h1>', false],
            'deny_file_scheme' => ['file:///etc/passwd', false],
            'deny_invalid_text' => ['not-a-url', false],
        ];
    }

    /**
     * テストの意図:
     * redirect_path は内部遷移だけを許可し、外部 URL やブラウザ解釈が揺れる値は安全な既定値へ落とす。
     *
     * @dataProvider safeRedirectPathProvider
     */
    public function testSafeRedirectPath($redirect_path, string $expected): void
    {
        $this->assertSame($expected, UrlUtils::safeRedirectPath($redirect_path));
    }

    public function safeRedirectPathProvider(): array
    {
        return [
            'allow_internal_path' => ['/plugin/forms/index/12/34', '/plugin/forms/index/12/34'],
            'allow_internal_path_with_query_and_fragment' => [
                '/plugin/forms/index/12/34?frame_34_page=1#frame-34',
                '/plugin/forms/index/12/34?frame_34_page=1#frame-34',
            ],
            'allow_same_origin_absolute_url' => [
                'https://cms.example.jp/plugin/forms/index/12/34?x=1#frame-34',
                '/plugin/forms/index/12/34?x=1#frame-34',
            ],
            'convert_external_https_url_to_internal_path' => ['https://evil.example/login', '/login'],
            'convert_external_http_url_to_internal_path' => ['http://evil.example/login', '/login'],
            'convert_host_suffix_confusion_to_internal_path' => ['https://cms.example.jp.evil.example/login', '/login'],
            'deny_protocol_relative_url' => ['//evil.example/login', '/'],
            'deny_backslash_authority_url' => ['/\\evil.example/login', '/'],
            'deny_double_backslash_authority_url' => ['/\\\\evil.example/login', '/'],
            'deny_mixed_slash_authority_url' => ['/\\/evil.example/login', '/'],
            'deny_absolute_url_with_protocol_relative_path' => ['https://evil.example//login', '/'],
            'deny_absolute_url_with_backslash_path' => ['https://cms.example.jp/\\evil.example/login', '/'],
            'deny_backslash_in_internal_path' => ['/plugin/forms\\evil.example/login', '/'],
            'deny_javascript_scheme' => ['javascript:alert(1)', '/'],
            'deny_data_scheme' => ['data:text/html,<h1>login</h1>', '/'],
            'deny_plain_text' => ['not-a-url', '/'],
            'deny_crlf' => ["/plugin/forms/index/12/34\r\nLocation: https://evil.example", '/'],
            'deny_null_byte' => ["/plugin/forms/index/12/34\0", '/'],
            'deny_empty' => ['', '/'],
            'deny_null' => [null, '/'],
        ];
    }

    /**
     * テストの意図:
     * 絶対 URL は origin を信頼せず捨て、path/query/fragment の内部パスだけをリダイレクト先にする。
     */
    public function testSafeRedirectPathStripsOriginFromAbsoluteUrl(): void
    {
        $this->assertSame(
            '/plugin/forms/index/12/34?x=1#frame-34',
            UrlUtils::safeRedirectPath('https://cms.example.jp:8443/plugin/forms/index/12/34?x=1#frame-34')
        );
    }

    /**
     * テストの意図:
     * APP_URL と一致しない絶対 URL でも外部へは飛ばさず、内部パスとして扱う。
     */
    public function testSafeRedirectPathDoesNotDependOnAppUrl(): void
    {
        config(['app.url' => 'https://cms.example.jp']);

        $this->assertSame('/plugin/forms/index/12/34', UrlUtils::safeRedirectPath('https://attacker.example/plugin/forms/index/12/34'));
    }

    /**
     * テストの意図:
     * ディレクトリインストール環境では、redirect_path に含まれる設置ディレクトリを二重に付与しない。
     */
    public function testSafeRedirectPathStripsBasePathOnDirectoryInstall(): void
    {
        $original_request = app('request');
        $request = Request::create('/tmp/plugin/forms/thanks/17/443', 'GET', [], [], [], [
            'HTTP_HOST' => 'cms.example.jp',
            'HTTPS' => 'on',
            'SCRIPT_NAME' => '/tmp/index.php',
            'SCRIPT_FILENAME' => '/var/www/html/tmp/index.php',
        ]);

        app()->instance('request', $request);

        try {
            $this->assertSame(
                '/plugin/forms/thanks/17/443#frame-443',
                UrlUtils::safeRedirectPath('https://cms.example.jp/tmp/plugin/forms/thanks/17/443#frame-443')
            );

            $this->assertSame(
                '/plugin/forms/thanks/17/443#frame-443',
                UrlUtils::safeRedirectPath('/tmp/plugin/forms/thanks/17/443#frame-443')
            );
        } finally {
            app()->instance('request', $original_request);
        }
    }
}
