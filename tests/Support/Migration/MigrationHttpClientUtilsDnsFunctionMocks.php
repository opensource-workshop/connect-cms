<?php

namespace Tests\Support\Migration;

class MigrationHttpClientUtilsDnsFunctionMocks
{
    /** @var callable|null */
    private static $dns_get_record_callback;

    /** @var callable|null */
    private static $gethostbynamel_callback;

    public static function reset(): void
    {
        self::$dns_get_record_callback = null;
        self::$gethostbynamel_callback = null;
    }

    public static function setDnsGetRecordCallback(?callable $callback): void
    {
        self::$dns_get_record_callback = $callback;
    }

    public static function setGethostbynamelCallback(?callable $callback): void
    {
        self::$gethostbynamel_callback = $callback;
    }

    /**
     * @param string $host
     * @param int $type
     * @return array|false
     */
    public static function dnsGetRecord(string $host, int $type)
    {
        if (is_callable(self::$dns_get_record_callback)) {
            return call_user_func(self::$dns_get_record_callback, $host, $type);
        }

        if (\function_exists('dns_get_record')) {
            return \dns_get_record($host, $type);
        }

        return false;
    }

    /**
     * @param string $host
     * @return array|false
     */
    public static function gethostbynamel(string $host)
    {
        if (is_callable(self::$gethostbynamel_callback)) {
            return call_user_func(self::$gethostbynamel_callback, $host);
        }

        if (\function_exists('gethostbynamel')) {
            return \gethostbynamel($host);
        }

        return false;
    }
}

namespace App\Utilities\Migration;

use Tests\Support\Migration\MigrationHttpClientUtilsDnsFunctionMocks;

if (!function_exists(__NAMESPACE__ . '\\dns_get_record')) {
    /**
     * @param string $host
     * @param int $type
     * @return array|false
     */
    function dns_get_record($host, $type)
    {
        return MigrationHttpClientUtilsDnsFunctionMocks::dnsGetRecord((string) $host, (int) $type);
    }
}

if (!function_exists(__NAMESPACE__ . '\\gethostbynamel')) {
    /**
     * @param string $host
     * @return array|false
     */
    function gethostbynamel($host)
    {
        return MigrationHttpClientUtilsDnsFunctionMocks::gethostbynamel((string) $host);
    }
}
