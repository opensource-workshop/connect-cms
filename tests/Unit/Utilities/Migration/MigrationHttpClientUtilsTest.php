<?php

namespace Tests\Unit\Utilities\Migration;

use App\Utilities\Migration\MigrationHttpClientUtils;
use App\Utilities\Url\UrlUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use RuntimeException;
use Tests\TestCase;
use Tests\Support\Migration\MigrationHttpClientUtilsDnsFunctionMocks;

class MigrationHttpClientUtilsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MigrationHttpClientUtilsDnsFunctionMocks::reset();
    }

    protected function tearDown(): void
    {
        MigrationHttpClientUtilsDnsFunctionMocks::reset();
        parent::tearDown();
    }

    /**
     * HTTPクライアント生成時に connect 設定が反映されること
     *
     * @return void
     */
    public function testClientOptionsReflectConnectConfig()
    {
        config([
            'connect.CURL_TIMEOUT' => 7,
            'connect.HTTPPROXYTUNNEL' => true,
            'connect.PROXY' => 'proxy.example.local',
            'connect.PROXYPORT' => '8080',
            'connect.PROXYUSERPWD' => 'user:pass',
        ]);

        $options = MigrationHttpClientUtils::buildClientOptions();

        $this->assertSame(false, $options['http_errors']);
        $this->assertSame(false, $options['allow_redirects']);
        $this->assertSame(7.0, $options['timeout']);
        $this->assertSame('http://user:pass@proxy.example.local:8080', $options['proxy']);
    }

    /**
     * ランタイム指定でプロキシを無効化できること
     *
     * @return void
     */
    public function testClientOptionsCanDisableProxyWithRuntimeOption()
    {
        config([
            'connect.CURL_TIMEOUT' => 7,
            'connect.HTTPPROXYTUNNEL' => true,
            'connect.PROXY' => 'proxy.example.local',
            'connect.PROXYPORT' => '8080',
            'connect.PROXYUSERPWD' => 'user:pass',
        ]);

        $options = MigrationHttpClientUtils::buildClientOptions(['use_proxy' => false]);

        $this->assertSame(false, $options['http_errors']);
        $this->assertSame(false, $options['allow_redirects']);
        $this->assertSame(7.0, $options['timeout']);
        $this->assertArrayNotHasKey('proxy', $options);
    }

    /**
     * HTTPクライアントを生成できること
     *
     * @return void
     */
    public function testHttpClientCanBeCreated()
    {
        config(['connect.HTTPPROXYTUNNEL' => false]);

        $client = MigrationHttpClientUtils::createClient();

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * 文字列GETでレスポンス情報が返ること
     *
     * @return void
     */
    public function testGetReturnsResponseInformation()
    {
        config(['connect.HTTPPROXYTUNNEL' => false]);

        $captured_request = [];
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturnCallback(function ($method, $url, $options) use (&$captured_request) {
                $captured_request = compact('method', 'url', 'options');
                $options['on_stats']($this->fakeStats(['primary_ip' => '8.8.8.8']));

                return new Response(200, ['Location' => '/next'], 'hello');
            });

        $response = MigrationHttpClientUtils::get($client, 'https://8.8.8.8');

        $this->assertSame('GET', $captured_request['method']);
        $this->assertSame('https://8.8.8.8', $captured_request['url']);
        $this->assertArrayHasKey('on_stats', $captured_request['options']);
        $this->assertTrue(is_callable($captured_request['options']['on_stats']));
        $this->assertSame('hello', $response['body']);
        $this->assertSame(200, $response['http_code']);
        $this->assertSame('/next', $response['location']);
        $this->assertSame('', $response['content_disposition']);
    }

    /**
     * DNS pinning では dual-stack の解決結果をまとめて固定し、IPv4/IPv6 の片方へ強制しないこと
     *
     * @return void
     */
    public function testGetAppliesDnsPinningWithAllGlobalResolvedAddresses()
    {
        if (!defined('CURLOPT_RESOLVE')) {
            $this->markTestSkipped('CURLOPT_RESOLVE が利用できません。');
        }

        config(['connect.HTTPPROXYTUNNEL' => false]);

        MigrationHttpClientUtilsDnsFunctionMocks::setDnsGetRecordCallback(function ($host, $type) {
            $this->assertSame('example.com', $host);
            $this->assertSame(DNS_A + DNS_AAAA, $type);

            return [
                ['ipv6' => '2001:4860:4860::8888'],
                ['ip' => '8.8.8.8'],
            ];
        });
        MigrationHttpClientUtilsDnsFunctionMocks::setGethostbynamelCallback(function ($host) {
            $this->fail('dns_get_record() 成功時に gethostbynamel() は呼ばれない想定です。');
            return false;
        });

        $captured_request = [];
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturnCallback(function ($method, $url, $options) use (&$captured_request) {
                $captured_request = compact('method', 'url', 'options');
                $options['on_stats']($this->fakeStats(['primary_ip' => '2001:4860:4860::8888']));

                return new Response(200, [], 'hello');
            });

        $response = MigrationHttpClientUtils::get($client, 'https://example.com/path');

        $this->assertSame('hello', $response['body']);
        $this->assertArrayHasKey('curl', $captured_request['options']);
        $this->assertArrayHasKey(CURLOPT_RESOLVE, $captured_request['options']['curl']);
        $this->assertContains(
            'example.com:443:[2001:4860:4860::8888],8.8.8.8',
            $captured_request['options']['curl'][CURLOPT_RESOLVE]
        );
    }

    /**
     * IDN URL は DNS pinning 用にも punycode ホスト名へ正規化されること
     *
     * @return void
     */
    public function testGetNormalizesIdnHostnameForDnsPinning()
    {
        if (!defined('CURLOPT_RESOLVE')) {
            $this->markTestSkipped('CURLOPT_RESOLVE が利用できません。');
        }
        if (!function_exists('idn_to_ascii')) {
            $this->markTestSkipped('idn_to_ascii() が利用できません。');
        }

        config(['connect.HTTPPROXYTUNNEL' => false]);

        $unicode_host = '例え.テスト';
        $idn_flags = defined('IDNA_DEFAULT') ? IDNA_DEFAULT : 0;
        $idn_variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
        $ascii_host = idn_to_ascii($unicode_host, $idn_flags, $idn_variant);
        $this->assertNotFalse($ascii_host);
        $ascii_host = strtolower($ascii_host);

        $captured_dns_host = '';
        MigrationHttpClientUtilsDnsFunctionMocks::setDnsGetRecordCallback(function ($host, $type) use (&$captured_dns_host, $ascii_host) {
            $captured_dns_host = $host;
            $this->assertSame($ascii_host, $host);
            $this->assertSame(DNS_A + DNS_AAAA, $type);

            return [
                ['ip' => '8.8.8.8'],
            ];
        });

        $captured_request = [];
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturnCallback(function ($method, $url, $options) use (&$captured_request) {
                $captured_request = compact('method', 'url', 'options');
                $options['on_stats']($this->fakeStats(['primary_ip' => '8.8.8.8']));

                return new Response(200, [], 'hello');
            });

        $response = MigrationHttpClientUtils::get($client, 'https://' . $unicode_host . '/path');

        $this->assertSame('hello', $response['body']);
        $this->assertSame($ascii_host, $captured_dns_host);
        $this->assertContains(
            $ascii_host . ':443:8.8.8.8',
            $captured_request['options']['curl'][CURLOPT_RESOLVE]
        );
    }

    /**
     * ファイルダウンロード時にsinkが利用され、Content-Dispositionが既存互換形式で返ること
     *
     * @return void
     */
    public function testDownloadToFileReturnsCompatibleContentDisposition()
    {
        config(['connect.HTTPPROXYTUNNEL' => false]);

        $sink_path = '/tmp/migration-http-client-utils-test.bin';
        $captured_request = [];
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturnCallback(function ($method, $url, $options) use ($sink_path, &$captured_request) {
                $captured_request = compact('method', 'url', 'options');
                $options['on_stats']($this->fakeStats(['primary_ip' => '1.1.1.1']));

                return new Response(200, [
                    'Content-Disposition' => "attachment;filename*=UTF-8''sample.txt",
                ], 'ignored-body');
            });

        $response = MigrationHttpClientUtils::downloadToFile($client, 'https://8.8.8.8/file', $sink_path);

        $this->assertSame('GET', $captured_request['method']);
        $this->assertSame('https://8.8.8.8/file', $captured_request['url']);
        $this->assertSame($sink_path, $captured_request['options']['sink']);
        $this->assertArrayHasKey('on_stats', $captured_request['options']);
        $this->assertSame('', $response['body']);
        $this->assertSame(200, $response['http_code']);
        $this->assertSame('', $response['location']);
        $this->assertSame("Content-Disposition: attachment;filename*=UTF-8''sample.txt", $response['content_disposition']);
    }

    /**
     * 非グローバルIPへ接続された場合は例外にすること
     *
     * @return void
     */
    public function testGetFailsWhenConnectedToNonGlobalIp()
    {
        config(['connect.HTTPPROXYTUNNEL' => false]);

        $client = $this->createMock(Client::class);
        $client->method('request')
            ->willReturnCallback(function ($method, $url, $options) {
                $options['on_stats']($this->fakeStats(['primary_ip' => '127.0.0.1']));
                return new Response(200, [], 'hello');
            });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Rejected non-global primary_ip');

        MigrationHttpClientUtils::get($client, 'https://8.8.8.8');
    }

    /**
     * DNSリバインディング相当（検証時OK/接続時NG）の状態を拒否すること
     *
     * 実際のDNS操作は行わず、TOCTOUの本質である
     * 「事前検証ではグローバル、実接続では内部IP」を擬似的に再現する。
     *
     * @return void
     */
    public function testGetRejectsDnsRebindingStylePrimaryIpChange()
    {
        config(['connect.HTTPPROXYTUNNEL' => false]);

        $url = 'http://8.8.8.8/example';
        $this->assertTrue(UrlUtils::isGlobalHttpUrl($url), '事前URL検証は通る前提');

        $captured_request = [];
        $client = $this->createMock(Client::class);
        $client->method('request')
            ->willReturnCallback(function ($method, $request_url, $options) use ($url, &$captured_request) {
                $captured_request = [
                    'method' => $method,
                    'url' => $request_url,
                    'options' => $options,
                ];
                // 実接続時に内部IPへ変化した状態を再現（DNSリバインディング相当）
                $options['on_stats']($this->fakeStats(['primary_ip' => '169.254.169.254']));

                return new Response(200, [], 'hello');
            });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Rejected non-global primary_ip');

        try {
            MigrationHttpClientUtils::get($client, $url);
        } finally {
            $this->assertSame('GET', $captured_request['method']);
            $this->assertSame($url, $captured_request['url']);
            $this->assertArrayHasKey('on_stats', $captured_request['options']);
        }
    }

    /**
     * HTTPPROXYTUNNEL=true でも実際にproxy未設定ならprimary_ip再検証を行うこと
     *
     * @return void
     */
    public function testGetFailsWhenProxyTunnelEnabledButProxyIsNotConfigured()
    {
        config([
            'connect.HTTPPROXYTUNNEL' => true,
            'connect.PROXY' => '',
        ]);

        $client = $this->createMock(Client::class);
        $client->method('request')
            ->willReturnCallback(function ($method, $url, $options) {
                // primary_ip を渡さない（直結通信かつ on_stats 未取得相当）
                $options['on_stats']($this->fakeStats([]));
                return new Response(200, [], 'direct');
            });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to verify primary_ip');

        MigrationHttpClientUtils::get($client, 'https://8.8.8.8');
    }

    /**
     * IPv4-mapped IPv6形式のprimary_ipでも元のIPv4がグローバルなら許可すること
     *
     * @return void
     */
    public function testGetAcceptsIpv4MappedIpv6PrimaryIpWhenMappedIpv4IsGlobal()
    {
        config(['connect.HTTPPROXYTUNNEL' => false]);

        $client = $this->createMock(Client::class);
        $client->method('request')
            ->willReturnCallback(function ($method, $url, $options) {
                $options['on_stats']($this->fakeStats(['primary_ip' => '::ffff:8.8.8.8']));
                return new Response(200, [], 'hello');
            });

        $response = MigrationHttpClientUtils::get($client, 'https://8.8.8.8');

        $this->assertSame('hello', $response['body']);
        $this->assertSame(200, $response['http_code']);
    }

    /**
     * プロキシ設定が有効な経由時はprimary_ip再検証をスキップすること
     *
     * @return void
     */
    public function testGetAllowsMissingPrimaryIpWhenUsingProxyTunnel()
    {
        config([
            'connect.HTTPPROXYTUNNEL' => true,
            'connect.PROXY' => 'proxy.example.local:8080',
        ]);

        $client = $this->createMock(Client::class);
        $client->method('request')
            ->willReturnCallback(function ($method, $url, $options) {
                // primary_ip を渡さない（プロキシ経由時想定）
                $options['on_stats']($this->fakeStats([]));
                return new Response(200, [], 'proxied');
            });

        $response = MigrationHttpClientUtils::get($client, 'https://8.8.8.8');

        $this->assertSame('proxied', $response['body']);
        $this->assertSame(200, $response['http_code']);
    }

    /**
     * ランタイム指定でプロキシを無効化した場合はprimary_ip再検証を行うこと
     *
     * @return void
     */
    public function testGetFailsWhenRuntimeOptionDisablesProxyEvenIfProxyConfigExists()
    {
        config([
            'connect.HTTPPROXYTUNNEL' => true,
            'connect.PROXY' => 'proxy.example.local:8080',
        ]);

        $client = $this->createMock(Client::class);
        $client->method('request')
            ->willReturnCallback(function ($method, $url, $options) {
                // use_proxy=false では直結相当として primary_ip 検証対象
                $options['on_stats']($this->fakeStats([]));
                return new Response(200, [], 'direct-runtime');
            });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to verify primary_ip');

        MigrationHttpClientUtils::get($client, 'https://8.8.8.8', ['use_proxy' => false]);
    }

    /**
     * ランタイム指定でプロキシ利用時はprimary_ip再検証をスキップすること
     *
     * @return void
     */
    public function testGetAllowsMissingPrimaryIpWhenRuntimeUseProxyIsTrue()
    {
        config([
            'connect.HTTPPROXYTUNNEL' => true,
            'connect.PROXY' => 'proxy.example.local:8080',
        ]);

        $client = $this->createMock(Client::class);
        $client->method('request')
            ->willReturnCallback(function ($method, $url, $options) {
                $options['on_stats']($this->fakeStats([]));
                return new Response(200, [], 'proxied-runtime');
            });

        $response = MigrationHttpClientUtils::get($client, 'https://8.8.8.8', ['use_proxy' => true]);

        $this->assertSame('proxied-runtime', $response['body']);
        $this->assertSame(200, $response['http_code']);
    }

    /**
     * on_stats へ渡す疑似statsオブジェクトを生成する
     *
     * @param array $handler_stats
     * @return object
     */
    private function fakeStats(array $handler_stats)
    {
        return new class ($handler_stats) {
            /** @var array */
            private $handler_stats;

            public function __construct(array $handler_stats)
            {
                $this->handler_stats = $handler_stats;
            }

            public function getHandlerStats(): array
            {
                return $this->handler_stats;
            }
        };
    }
}
