<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\IP;

class Utils
{
    const REGEXP_IP = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){0,3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/i';
    const REGEXP_CIDR = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){0,3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\/([0-9]|1[0-9]|2[0-9]|3[0-2])$/i';

    public static $privateNetworks = ['10/8', '172.16/12', '192.168/16']; // rfc1918
    public static $multicastNetworks = ['224/4']; // rfc3171
    public static $reservedNetworks = ['240/4']; // rfc1112
    public static $networkTypes = [ // rfc5735
        [
            'AddressBlock' => '0.0.0.0/8',
            'PresentUse' => '"This" Network',
            'Reference' => 'RFC 1122, Section 3.2.1.3',
        ], [
            'AddressBlock' =>'10.0.0.0/8',
            'PresentUse' =>'Private-Use Networks',
            'Reference' =>'RFC 1918'
        ], [
            'AddressBlock' =>'127.0.0.0/8',
            'PresentUse' =>'Loopback',
            'Reference' =>'RFC 1122, Section 3.2.1.3'
        ], [
            'AddressBlock' =>'169.254.0.0/16',
            'PresentUse' =>'Link Local',
            'Reference' =>'RFC 3927'
        ], [
            'AddressBlock' =>'172.16.0.0/12',
            'PresentUse' =>'Private-Use Networks',
            'Reference' =>'RFC 1918'
        ], [
            'AddressBlock' =>'192.0.0.0/24',
            'PresentUse' =>'IETF Protocol Assignments',
            'Reference' =>'RFC 5736'
        ], [
            'AddressBlock' =>'192.0.2.0/24',
            'PresentUse' =>'TEST-NET-1',
            'Reference' =>'RFC 5737'
        ], [
            'AddressBlock' =>'192.88.99.0/24',
            'PresentUse' =>'6to4 Relay Anycast',
            'Reference' =>'RFC 3068'
        ], [
            'AddressBlock' =>'192.168.0.0/16',
            'PresentUse' =>'Private-Use Networks',
            'Reference' =>'RFC 1918'
        ], [
            'AddressBlock' =>'198.18.0.0/15',
            'PresentUse' =>'Network Interconnect, Device Benchmark Testing',
            'Reference' =>'RFC 2544'
        ], [
            'AddressBlock' =>'198.51.100.0/24',
            'PresentUse' =>'TEST-NET-2',
            'Reference' =>'RFC 5737'
        ], [
            'AddressBlock' =>'203.0.113.0/24',
            'PresentUse' =>'TEST-NET-3',
            'Reference' =>'RFC 5737'
        ], [
            'AddressBlock' =>'224.0.0.0/4',
            'PresentUse' =>'Multicast',
            'Reference' =>'RFC 3171'
        ], [
            'AddressBlock' =>'240.0.0.0/4',
            'PresentUse' =>'Reserved for Future Use',
            'Reference' =>'RFC 1112, Section 4'
        ], [
            'AddressBlock' =>'255.255.255.255/32',
            'PresentUse' =>'Limited Broadcast',
            'Reference' =>'RFC 919, Section 7, RFC 922, Section 7'
        ]
    ];

    protected static $octetCount = 4;
    protected static $octetOffsets = [24, 16, 8, 0];
    protected static $octetMasks = [0xFF000000, 0x00FF0000, 0x0000FF00, 0x000000FF];

    public static function isLong($long): bool
    {
        return is_int($long) && ($long >= 0 && $long <= 0xFFFFFFFF);
    }

    public static function isString($addr): bool
    {
        return is_string($addr) && (bool)preg_match(self::REGEXP_IP, $addr);
    }

    public static function isCIDR($cidr): bool
    {
        return is_string($cidr) && (bool)preg_match(self::REGEXP_CIDR, $cidr);
    }

    public static function detectFormat($addr): string
    {
        if (self::isLong($addr)) {
            return 'numeric';
        }
        if (self::isString($addr)) {
            return 'string';
        }
        if (self::isCIDR($addr)) {
            return 'cidr';
        }

        return 'unknown';
    }

    public static function getLongPrefixFromCIDR($cidr): int
    {
        if (self::isCIDR($cidr) === false) {
            throw new \InvalidArgumentException('Wrong cidr format');
        }

        $cidrParts = explode('/', $cidr);

        return self::toLong($cidrParts[0]);
    }

    public static function getMaskBitsFromCIDR($cidr): int
    {
        if (self::isCIDR($cidr) === false) {
            throw new \InvalidArgumentException('Wrong cidr format');
        }

        $cidrParts = explode('/', $cidr);

        $mask = 0xFFFFFFFF << (32 - $cidrParts[1]);

        return (PHP_INT_SIZE == 8 ? $mask & 0x00000000FFFFFFFF : $mask);
    }

    public static function toLong(string $addr): int
    {
        if (self::isString($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr format');
        }

        $num = 0;
        $octets = explode('.', $addr, self::$octetCount);
        $restoredZeroOctetsCount = self::$octetCount - count($octets);

        for ($i=0; $i<$restoredZeroOctetsCount; $i++) {
            array_push($octets, "0");
        }

        for ($i=0; $i<self::$octetCount; $i++) {
            $num |= intval($octets[$i]) << self::$octetOffsets[$i];
        }

        return $num;
    }

    public static function toString(int $addr): string
    {
        if (self::isLong($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr format');
        }

        $octets = [];

        for ($i=0; $i<self::$octetCount; $i++) {
            array_push($octets, ($addr & self::$octetMasks[$i]) >> self::$octetOffsets[$i]);
        }

        return implode('.', $octets);
    }

    public static function toBinaryString(int $addr): string
    {
        if ($addr < 0 || $addr > 0xFFFFFFFF) {
            throw new \OutOfRangeException('Argument $nMask must be between 0 and 0xFFFFFFFF');
        }

        return str_pad(decbin($addr), 32, '0', STR_PAD_LEFT);
    }
}
