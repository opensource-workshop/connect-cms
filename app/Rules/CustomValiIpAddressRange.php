<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Utilities\Network\IpAddressUtils;

/**
 * IPアドレス制限の指定形式チェック
 * IPv4/IPv6アドレス、CIDR形式（例: 192.168.1.0/24, 2001:db8::/64）を許可する。
 *
 * - single(): 1件指定。"*"（全許可）も指定可能（ログイン制限、API）
 * - multiple(): カンマ区切りで複数指定可能。"*"は指定不可（ページのIPアドレス制限）
 */
class CustomValiIpAddressRange implements Rule
{
    /** @var bool カンマ区切りで複数指定を許可するか */
    private $allow_multiple;

    /** @var bool "*"（全許可）を許可するか */
    private $allow_wildcard;

    private function __construct($allow_multiple, $allow_wildcard)
    {
        $this->allow_multiple = $allow_multiple;
        $this->allow_wildcard = $allow_wildcard;
    }

    /**
     * 1件指定（"*"可）
     */
    public static function single()
    {
        return new self(false, true);
    }

    /**
     * カンマ区切りで複数指定（"*"不可）
     */
    public static function multiple()
    {
        return new self(true, false);
    }

    /**
     * @param  string  $attribute 項目名
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // 空は必須チェック等、他のルールに任せる
        if ($value === null || $value === '') {
            return true;
        }
        // 配列など文字列以外は不正
        if (!is_string($value)) {
            return false;
        }
        if (trim($value) === '') {
            return true;
        }

        $ip_addresses = $this->allow_multiple ? explode(',', $value) : [$value];
        foreach ($ip_addresses as $ip_address) {
            if (!$this->allow_wildcard && trim($ip_address) === '*') {
                return false;
            }
            if (!IpAddressUtils::isValidRule($ip_address)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function message()
    {
        $message = ':attributeはIPv4/IPv6アドレス、またはCIDR形式（例: 192.168.1.0/24, 2001:db8::/64）で指定してください。';
        if ($this->allow_multiple) {
            $message .= 'カンマ区切りで複数指定できます。*での指定はできません。';
        }
        return $message;
    }
}
