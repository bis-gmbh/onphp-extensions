<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\IP;

class Utils6
{
    public static $addressTypes = [ // rfc4291
        [
            'IPv6Notation' => '::/128',
            'AddressType' => 'Unspecified',
        ], [
            'IPv6Notation' => '::1/128',
            'AddressType' => 'Loopback',
        ], [
            'IPv6Notation' => 'FF00::/8',
            'AddressType' => 'Multicast',
        ], [
            'IPv6Notation' => 'FE80::/10',
            'AddressType' => 'Link-Local unicast',
        ]
    ];

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isNumeric($value)
    {
        return is_numeric($value) && ($value >= 0);
    }

    /**
     * @param mixed $addr
     * @return bool
     */
    public static function isTextual($addr)
    {
        try {
            $result = inet_pton($addr);
            return $result !== false;
        } catch (\Exception $e) {
            // nop
        }

        return false;
    }

    /**
     * @param mixed $cidr
     * @return bool
     */
    public static function isCIDR($cidr)
    {
        if (false === is_string($cidr)) {
            return false;
        }

        $cidrParts = explode('/', $cidr);

        if (
            count($cidrParts) === 2
            && self::isTextual($cidrParts[0])
            && $cidrParts[1] >= 0 && $cidrParts[1] <= 128
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $addr
     * @return string
     */
    public static function detectFormat($addr)
    {
        if (self::isNumeric($addr)) {
            return 'numeric';
        }
        if (self::isTextual($addr)) {
            return 'textual';
        }
        if (self::isCIDR($addr)) {
            return 'cidr';
        }

        return 'unknown';
    }

    /**
     * @param $cidr
     * @return resource|object
     */
    public static function getNumericPrefixFromCIDR($cidr)
    {
        if (self::isCIDR($cidr) === false) {
            throw new \InvalidArgumentException('Wrong cidr format');
        }

        $cidrParts = explode('/', $cidr);

        return self::toNumeric($cidrParts[0]);
    }

    /**
     * @param $cidr
     * @return resource|object
     */
    public static function getMaskBitsFromCIDR($cidr)
    {
        if (self::isCIDR($cidr) === false) {
            throw new \InvalidArgumentException('Wrong cidr format');
        }

        $cidrParts = explode('/', $cidr);

        $mask = gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
        for ($i=0; $i<(128 - $cidrParts[1]); $i++) {
            gmp_clrbit($mask, $i);
        }

        return $mask;
    }

    /**
     * @param string $addr
     * @return resource|object
     */
    public static function toNumeric($addr)
    {
        if (self::isTextual($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr format');
        }

        return gmp_init('0x' . unpack('H*', inet_pton($addr))[1]);
    }

    /**
     * @param resource|object $addr
     * @return string
     */
    public static function toTextual($addr)
    {
        if (self::isGMP($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr argument');
        }

        $hex = str_pad(gmp_strval($addr, 16), 32, '0', STR_PAD_LEFT);
        $packed = hex2bin($hex);

        return inet_ntop($packed);
    }

    /**
     * @param resource|object $addr
     * @return string
     */
    public static function toBinaryString($addr)
    {
        if (self::isGMP($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr argument');
        }

        return str_pad(decbin(gmp_strval($addr)), 128, '0', STR_PAD_LEFT);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private static function isGMP($value)
    {
        if (
            version_compare(PHP_VERSION, '5.6.0') >= 0
                ? ($value instanceof \GMP)
                : is_resource($value)
        ) {
            return true;
        }

        return false;
    }
}
