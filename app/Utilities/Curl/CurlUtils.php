<?php

namespace App\Utilities\Curl;

use Illuminate\Support\Facades\Log;

class CurlUtils
{
    /**
     * cURLを実行する。
     *
     * @param string $url URL
     * @param string $method HTTPメソッド
     * @param array $params パラメータ
     * @param array $header ヘッダー
     * @return array 返り値 ['info' => array, 'body' => string]
     * @see https://www.php.net/manual/ja/function.curl-getinfo.php
     */
    public static function execute(string $url, string $method = 'GET', array $params = [], array $header = []): array
    {
        return self::executeCurl(self::buildOptions($url, $method, $params, $header));
    }

    /**
     * cURLを呼び出す。
     *
     * @param array cURLのオプション
     * @return array 返り値
     */
    private static function executeCurl(array $options): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);

        // cURLの実行失敗
        // ネットワーク等、アプリケーション以外の異常であるため、例外を投げる
        if ($result === false) {
            $errno = curl_errno($ch);
            $err = curl_error($ch);
            $error_message = "cURL [{$options[CURLOPT_CUSTOMREQUEST]}] {$options[CURLOPT_URL]} : failed. [Error:$errno] $err";
            Log::error($error_message);
            throw new \RuntimeException($error_message);
        }

        $info = curl_getinfo($ch);
        $http_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        // 実行結果のログを出力する（Response codeが200番台なら成功）
        if (200 <= $http_code  && $http_code <= 299) {
            Log::debug("cURL [{$options[CURLOPT_CUSTOMREQUEST]}] {$options[CURLOPT_URL]} : succeed. Response code: $http_code\n$result");
        } else {
            Log::info("cURL [{$options[CURLOPT_CUSTOMREQUEST]}] {$options[CURLOPT_URL]} : failed. Response code: $http_code\n$result");
        }

        return [
            'info' => $info,
            'body' => $result,
        ];
    }

    /**
     * cURLのオプションを作成する。
     *
     * @param string $url URL
     * @param string $method HTTPメソッド
     * @param array $params パラメータ
     * @param array $header ヘッダー
     * @return array cURLのオプション
     */
    private static function buildOptions(string $url, string $method, array $params, array $header): array
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => config('connect.CURL_TIMEOUT'),
        ];

        // ヘッダー設定
        if (!empty($header)) {
            $options[CURLOPT_HTTPHEADER] = $header;
        }

        // Proxy設定
        if (config('connect.HTTPPROXYTUNNEL')) {
            $options = self::addProxyOptions($options);
        }

        // パラメータ設定
        $options = self::addParams($options, $params);

        return $options;
    }

    /**
     * cURLのオプションにパラメータを設定する。
     *
     * @param array $options cURLのオプション
     * @param array $params パラメータ
     * @return array パラメータ設定済みのcURLオプション
     */
    private static function addParams(array $options, array $params): array
    {

        if (empty($params)) {
            return $options;
        }

        if ($options[CURLOPT_CUSTOMREQUEST] === 'GET') {
            $options[CURLOPT_URL] = $options[CURLOPT_URL] . '?' . http_build_query($params);
        } else {
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        }

        return $options;
    }

    /**
     * cURLオプションにプロキシ設定を追加する。
     *
     * @param array $options cURLオプション
     * @return array プロキシ設定追加済みのcURLオプション
     */
    private static function addProxyOptions(array $options)
    {
        $options[CURLOPT_HTTPPROXYTUNNEL] = config('connect.HTTPPROXYTUNNEL');
        $options[CURLOPT_PROXYPORT] = config('connect.PROXYPORT');
        $options[CURLOPT_PROXY] = config('connect.PROXY');
        $options[CURLOPT_PROXYUSERPWD] = config('connect.PROXYUSERPWD');

        return $options;
    }
}
