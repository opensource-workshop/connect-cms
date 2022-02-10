<?php

namespace Tests\Unit;

use App\Traits\ExeceutesWebApi;
use RuntimeException;
use Tests\TestCase;

class ExeceutesWebApiTest extends TestCase
{

    private $trait;
    private $mock_url = 'https://httpbin.org';

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Traitをテストするため、モックを用意する
        $this->trait = $this->getMockForTrait(ExeceutesWebApi::class);
    }

    /**
     * プロパティ：タイムアウト秒数のテスト
     */
    public function testTimeoutAttribute()
    {
        $timeout = $this->trait->getTimeout();
        $this->assertEquals(120, $timeout);

        $this->trait->setTimeout(180);
        $timeout = $this->trait->getTimeout();
        $this->assertEquals(180, $timeout);
    }

    /**
     * 接続不可のテスト
     *
     * @return void
     */
    public function testCouldNotResolveHost()
    {
        $url = 'https://aqwsderftgyhujiko';

        // Proxy Off
        config(['connect.HTTPPROXYTUNNEL' => false]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cURL [GET] https://aqwsderftgyhujiko : failed. [Error:6] Could not resolve host: aqwsderftgyhujiko');
        $this->trait->executeCurl($url);
    }

    /**
     * 接続タイムアウトのテスト
     *
     * @return void
     */
    public function testTimeOut()
    {
        // Proxy Off
        config(['connect.HTTPPROXYTUNNEL' => false]);

        $url = $this->mock_url . '/delay/10';
        $this->trait->setTimeout(5);
        $this->expectException(RuntimeException::class);
        // Exceptionのメッセージはテスト済みなので省略
        $this->trait->executeCurl($url);
    }

    /**
     * WebAPI実行(GET)のテスト
     *
     * @return void
     */
    public function testGet()
    {
        // Proxy Off
        config(['connect.HTTPPROXYTUNNEL' => false]);
        $url = $this->mock_url . '/get';
        $params = [
            'id' => 'ABCD1234',
            'name' => 'John',
        ];

        // パラメーターなし
        $return = $this->trait->executeCurl($url, 'GET');
        $body = $return['body'];
        $json = json_decode($body, true);
        $this->assertEquals([], $json['args']);
        $info = $return['info'];
        $this->assertEquals(200, $info['http_code']);

        // パラメーターあり
        $return = $this->trait->executeCurl($url, 'GET', $params);
        $body = $return['body'];
        $json = json_decode($body, true);
        $this->assertEquals($params, $json['args']);
        $info = $return['info'];
        $this->assertEquals(200, $info['http_code']);
    }

    /**
     * WebAPI実行(GET)のテスト
     * ヘッダーの確認
     *
     * @return void
     */
    public function testHeader()
    {
        // Proxy Off
        config(['connect.HTTPPROXYTUNNEL' => false]);
        $url = $this->mock_url . '/headers';

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer 1234567890abcdefg',
        ];

        $return = $this->trait->executeCurl($url, 'GET', [], $headers);

        $body = $return['body'];
        $json = json_decode($body, true);
        $this->assertEquals('application/json', $json['headers']['Accept']);
        $this->assertEquals('Bearer 1234567890abcdefg', $json['headers']['Authorization']);

        $info = $return['info'];
        $this->assertEquals(200, $info['http_code']);
    }

    /**
     * WebAPI実行(POST)のテスト
     *
     * @return void
     */
    public function testPost()
    {
        // Proxy Off
        config(['connect.HTTPPROXYTUNNEL' => false]);
        $url = $this->mock_url . '/post';
        $params = [
            'id' => 'ABCD1234',
            'name' => 'John',
        ];

        // パラメーターなし
        $return = $this->trait->executeCurl($url, 'POST');
        $body = $return['body'];
        $json = json_decode($body, true);
        $this->assertEquals([], $json['form']);
        $info = $return['info'];
        $this->assertEquals(200, $info['http_code']);

        // パラメーターあり
        $return = $this->trait->executeCurl($url, 'POST', $params);
        $body = $return['body'];
        $json = json_decode($body, true);
        $this->assertEquals($params, $json['form']);
        $info = $return['info'];
        $this->assertEquals(200, $info['http_code']);
    }

    /**
     * WebAPI実行（レスポンスコード:404）のテスト
     *
     * @return void
     */
    public function test404()
    {
        // Proxy Off
        config(['connect.HTTPPROXYTUNNEL' => false]);
        $url = $this->mock_url . '/status/404';

        // GET
        $return = $this->trait->executeCurl($url);

        $info = $return['info'];
        $this->assertEquals(404, $info['http_code']);

        // POST
        $return = $this->trait->executeCurl($url, 'POST');

        $info = $return['info'];
        $this->assertEquals(404, $info['http_code']);
    }

    /**
     * Proxyを使った接続テスト
     *
     * @return void
     */
    public function testUseProxy()
    {
        // Proxy Off
        config(['connect.HTTPPROXYTUNNEL' => true]);

        $url = $this->mock_url . '/get';
        $return = $this->trait->executeCurl($url);

        $body = $return['body'];
        $json = json_decode($body, true);
        // 接続元アドレスを確認する
        $this->assertEquals(config('connect.PROXY'), $json['origin']);

        $info = $return['info'];
        $this->assertEquals(200, $info['http_code']);
    }
}
