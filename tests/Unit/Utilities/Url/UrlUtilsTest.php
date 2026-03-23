<?php

namespace Tests\Unit\Utilities\Url;

use App\Utilities\Url\UrlUtils;
use Tests\TestCase;

class UrlUtilsTest extends TestCase
{
    /**
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
}
