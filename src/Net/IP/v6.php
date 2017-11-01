<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\IP;

class v6 extends BaseAddress
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

    public function __construct($anyFormat = null, $mask = null)
    {
        if ( ! defined('AF_INET6')) {
            throw new \RuntimeException('PHP must be compiled with --enable-ipv6 option');
        }
        if ( ! extension_loaded('gmp')) {
            throw new \RuntimeException('Extension GMP not loaded');
        }

        $this->version = 6;
        $this->addr = gmp_init(0);
        $this->mask = gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
        $this->maxPrefixLength = 128;

        if ($anyFormat !== null) {
            $this->assign($anyFormat, $mask);
        }
    }

    public function assign($anyFormat, $maskString = null): Address
    {
        if (Utils6::detectFormat($anyFormat) === 'numeric') {
            if ($maskString !== null) {
                throw new \InvalidArgumentException('Mask argument not allowed');
            }
            $this->addr = gmp_init($anyFormat);
        } else if (Utils6::detectFormat($anyFormat) === 'textual') {
            if ($maskString !== null) {
                if (Utils6::detectFormat($maskString) === 'textual') {
                    $this->mask = gmp_init(self::toNumeric($maskString), 16);
                } else {
                    throw new \InvalidArgumentException('Mask argument must have textual format');
                }
            }
            $this->addr = gmp_init(self::toNumeric($anyFormat), 16);
        } else if (Utils6::detectFormat($anyFormat) === 'cidr') {
            if ($maskString !== null) {
                throw new \InvalidArgumentException('Mask argument not allowed');
            }
            $this->addr = $this->getNumericPrefixFromCIDR($anyFormat);
            $this->mask = $this->getMaskBitsFromCIDR($anyFormat);
        } else {
            throw new \InvalidArgumentException('Wrong arguments');
        }

        return $this;
    }

    /**
     * @param string $addr hexadecimal string address representation
     * @return string
     */
    public static function toNumeric($addr)
    {
        if (Utils6::isTextual($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr format');
        }

        return '0x' . current(unpack('H32', inet_pton($addr)));
    }

    /**
     * @param int|string $addr numeric address
     * @return string
     */
    public static function toTextual($addr)
    {
        if (Utils6::isNumeric($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr argument');
        }

        $hex = str_pad(gmp_strval(gmp_init($addr), 16), 32, '0', STR_PAD_LEFT);
        $packed = hex2bin($hex);

        return inet_ntop($packed);
    }

    /**
     * @param int|string $addr numeric address
     * @return string
     */
    public static function toBinaryString($addr)
    {
        if (Utils6::isNumeric($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr argument');
        }

        return '0b' . str_pad(decbin(gmp_strval(gmp_init($addr), 10)), 128, '0', STR_PAD_LEFT);
    }

    /**
     * @return string hexadecimal address representation
     */
    public function numeric()
    {
        return '0x' . str_pad(gmp_strval($this->addr, 16), 32, '0', STR_PAD_LEFT);
    }

    /**
     * @return string hexadecimal netmask representation
     */
    public function netmask()
    {
        return '0x' . str_pad(gmp_strval($this->mask, 16), 32, '0', STR_PAD_LEFT);
    }

    /**
     * @return string hexadecimal negative netmask value representation
     */
    public function negativeMask()
    {
        return '0x' . str_pad(gmp_strval($this->gmp_not($this->mask), 16), 32, '0', STR_PAD_LEFT);
    }

    public function prefixLength()
    {
        for (
            $i = 0, $bitsCount = 0;
            $i < $this->maxPrefixLength;
            gmp_testbit($this->mask, $i) ? $bitsCount++ : null, $i++
        );

        return $bitsCount;
    }

    /**
     * @return v6
     */
    public function first()
    {
        return new self(gmp_strval(gmp_and($this->addr, $this->mask), 10));
    }

    /**
     * @return v6
     */
    public function last()
    {
        return new self(gmp_strval(gmp_add(gmp_and($this->addr, $this->mask), $this->gmp_not($this->mask)), 10));
    }

    /**
     * @return string decimal value
     */
    public function numAddrs()
    {
        $prefixLength = $this->prefixLength();

        if ($prefixLength === $this->maxPrefixLength) {
            $num = gmp_init(1);
        } else if ($prefixLength === 0) {
            $num = $this->gmp_not($this->mask);
        } else {
            $num = gmp_add($this->gmp_not($this->mask), 1);
        }
        return gmp_strval($num, 10);
    }

    /**
     * @return resource|string
     */
    public function numHosts()
    {
        $num = gmp_init($this->numAddrs());
        return (gmp_cmp($num, gmp_init(2)) > 0) ? gmp_strval(gmp_sub($num, gmp_init(2)), 10) : '1';
    }

    public function ltEq(Address $addr)
    {
        $beginScopeCmp = gmp_cmp($this->addr, gmp_init($addr->numeric()));

        return $beginScopeCmp === 0 || $beginScopeCmp < 0;
    }

    public function gtEq(Address $addr)
    {
        $endScopeCmp = gmp_cmp($this->addr, gmp_init($addr->numeric()));

        return $endScopeCmp === 0 || $endScopeCmp > 0;
    }

    public function addr()
    {
        return self::toTextual($this->numeric());
    }

    public function mask()
    {
        return self::toTextual($this->netmask());
    }

    public function cidr()
    {
        return self::toTextual($this->numeric()) . '/' . $this->prefixLength();
    }

    public function range()
    {
        return self::toTextual($this->first()->numeric()) . ' - ' . self::toTextual($this->last()->numeric());
    }

    public function reverse()
    {
        // reverse v4 part, if exists
        $octets = explode('.', self::toTextual($this->addr));
        $reversedV4Part = implode('.', array_reverse($octets));

        $groups = explode(':', $reversedV4Part);
        return implode(':', array_reverse($groups)) . '.ip6.arpa.';
    }

    public function netType()
    {
        for ($i=0; $i<count(self::$addressTypes); $i++) {
            if ($this->contains(self::create(self::$addressTypes[$i]['IPv6Notation']))) {
                return self::$addressTypes[$i]['AddressType'];
            }
        }

        return 'Global Unicast';
    }

    /**
     * @return string full textual address
     */
    public function full()
    {
        $unpaddedHex = gmp_strval($this->addr, 16);
        $solidFullHex = str_pad($unpaddedHex, 32, '0', STR_PAD_LEFT);
        $groups = str_split($solidFullHex, 4);

        return implode(':', $groups);
    }

    /**
     * @return string full textual address mixed with v4 address
     */
    public function full4()
    {
        $unpaddedHex = gmp_strval($this->addr, 16);
        $solidFullHex = str_pad($unpaddedHex, 32, '0', STR_PAD_LEFT);
        $groups = str_split($solidFullHex, 4);

        $octets = array_merge(str_split($groups[6], 2), str_split($groups[7], 2));
        $octets = array_map('hexdec', $octets);
        $v4 = implode('.', $octets);
        unset($groups[7]);
        $groups[6] = $v4;

        return implode(':', $groups);
    }

    /**
     * @return string compressed textual address
     */
    public function compressed()
    {
        $unpaddedHex = gmp_strval($this->addr, 16);
        $solidFullHex = str_pad($unpaddedHex, 32, '0', STR_PAD_LEFT);
        $groups = str_split($solidFullHex, 4);
        $groups = array_map(function ($a) { return preg_replace('/^0{1,3}/i', '', $a); }, $groups);
        $compactFullAddr = implode(':', $groups);

        return $this->compress($compactFullAddr);
    }

    /**
     * @return string compressed textual address mixed with v4 address
     */
    public function compressed4()
    {
        $unpaddedHex = gmp_strval($this->addr, 16);
        $solidFullHex = str_pad($unpaddedHex, 32, '0', STR_PAD_LEFT);
        $groups = str_split($solidFullHex, 4);

        $v6Part = array_slice($groups, 0, 6);
        $v6Part = array_map(function ($a) { return preg_replace('/^0{1,3}/i', '', $a); }, $v6Part);
        $full_addr = implode(':', $v6Part);

        $v4Part = array_slice($groups, 6, 2);
        $octets = array_merge(str_split($v4Part[0], 2), str_split($v4Part[1], 2));
        $octets = array_map('hexdec', $octets);
        $v4 = implode('.', $octets);

        $compressed = $this->compress($full_addr . ':' . $v4);

        // special case, when v4 part starts from ':0.', then we get ':.'. Revert it back:
        $compressed = str_replace(':.', ':0.', $compressed);

        // special case, when v6 part is unspecified (::)
        $compressed = preg_replace('/^:(\d+)/i', '::$1', $compressed, 1);

        return $compressed;
    }

    /**
     * @param string $hexAddr textual address in hexadecimal format
     * @return string
     */
    private function compress($hexAddr)
    {
        $zeroChains = $leadingZeroChain = $trailingZeroChain = $allZeroChain = $maxChains = [];

        // find max sequence of zero groups for replacement
        $cmpZeroCount = function ($a, $b) {
            $za = $zb = 0;

            for ($i=0; $i<strlen($a); $i++) {
                if ($a[$i] == '0') {
                    $za++;
                }
            }
            for ($i=0; $i<strlen($b); $i++) {
                if ($b[$i] == '0') {
                    $zb++;
                }
            }

            if ($za == $zb) {
                return 0;
            }

            return ($za < $zb) ? -1 : 1;
        };

        preg_match_all('/:0{1,4}(:0{1,4}){0,5}:/i', $hexAddr, $zeroChains);
        preg_match('/^0{1,4}:(0{1,4}:){0,6}/i', $hexAddr, $leadingZeroChain);
        preg_match('/(:0{1,4}){0,6}:0{1,4}$/i', $hexAddr, $trailingZeroChain);
        preg_match('/^0{1,4}(:0{1,4}){7}$/i', $hexAddr, $allZeroChain);

        if (isset($zeroChains[0])) {
            usort($zeroChains[0], $cmpZeroCount);
            $maxChain = end($zeroChains[0]);
            if ($maxChain) {
                $maxChains[] = $maxChain;
            }
        }
        if (isset($leadingZeroChain[0])) {
            $maxChains[] = $leadingZeroChain[0];
        }
        if (isset($trailingZeroChain[0])) {
            $maxChains[] = $trailingZeroChain[0];
        }
        if (isset($allZeroChain[0])) {
            $maxChains[] = $allZeroChain[0];
        }

        if (count($maxChains) > 0) {
            usort($maxChains, $cmpZeroCount);
            $maxChain = end($maxChains);
            if ($maxChain) {
                return preg_replace(sprintf('/%s/i', $maxChain), '::', $hexAddr, 1);
            }
        }

        return $hexAddr;
    }

    /**
     * @param $cidr
     * @return string
     */
    private function getNumericPrefixFromCIDR($cidr)
    {
        if (Utils6::isCIDR($cidr) === false) {
            throw new \InvalidArgumentException('Wrong cidr format');
        }

        $cidrParts = explode('/', $cidr);

        return self::toNumeric($cidrParts[0]);
    }

    /**
     * @param $cidr
     * @return resource|object
     */
    private function getMaskBitsFromCIDR($cidr)
    {
        if (Utils6::isCIDR($cidr) === false) {
            throw new \InvalidArgumentException('Wrong cidr format');
        }

        $cidrParts = explode('/', $cidr);

        $mask = gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
        for ($i=0; $i<($this->maxPrefixLength - $cidrParts[1]); $i++) {
            gmp_clrbit($mask, $i);
        }

        return $mask;
    }

    /**
     * @param resource|\GMP $value
     * @return resource|\GMP
     */
    private function gmp_not($value)
    {
        for (
            $i = 0;
            $i < $this->maxPrefixLength;
            gmp_testbit($value, $i) ? gmp_clrbit($value, $i) : gmp_setbit($value, $i), $i++
        );

        return $value;
    }
}
