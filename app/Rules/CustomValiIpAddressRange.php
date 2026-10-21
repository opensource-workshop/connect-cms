<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Utilities\Network\IpAddressUtils;

/**
 * IPアドレス制限の指定形式チェック
 * "*"、IPv4/IPv6アドレス、CIDR形式（例: 192.168.1.0/24, 2001:db8::/64）を許可する。
 */
class CustomValiIpAddressRange implements Rule
{
    /** @var bool カンマ区切りで複数指定を許可するか */
    private $allow_multiple;

    /**
     * @param bool $allow_multiple カンマ区切りで複数指定を許可するか
     */
    public function __construct($allow_multiple = false)
    {
        $this->allow_multiple = $allow_multiple;
    }

    /**
     * @param  string  $attribute 項目名
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // 空は必須チェック等、他のルールに任せる
        if ($value === null || trim($value) === '') {
            return true;
        }

        $ip_addresses = $this->allow_multiple ? explode(',', $value) : [$value];
        foreach ($ip_addresses as $ip_address) {
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
        $multiple = $this->allow_multiple ? '（カンマ区切りで複数指定可）' : '';
        return ':attributeはIPv4/IPv6アドレス、またはCIDR形式（例: 192.168.1.0/24, 2001:db8::/64）で指定してください。' . $multiple;
    }
}
