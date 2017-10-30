<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\IP;

class v4 extends BaseAddress
{
    public function __construct($anyFormat = null, $mask = null)
    {
        $this->version = 4;
        $this->addr = 0;
        $this->mask = 0xFFFFFFFF;
        $this->maxPrefixLength = 32;

        if ($anyFormat !== null) {
            $this->assign($anyFormat, $mask);
        }
    }

    public function assign($anyFormat, $maskString = null): Address
    {
        if (Utils::detectFormat($anyFormat) === 'numeric') {
            if ($maskString !== null) {
                throw new \InvalidArgumentException('Mask argument not allowed');
            }
            $this->addr = $anyFormat;
        } else if (Utils::detectFormat($anyFormat) === 'string') {
            if (Utils::detectFormat($maskString) === 'string') {
                $this->mask = Utils::toLong($maskString);
            }
            $this->addr = Utils::toLong($anyFormat);
        } else if (Utils::detectFormat($anyFormat) === 'cidr') {
            if ($maskString !== null) {
                throw new \InvalidArgumentException('Mask argument not allowed');
            }
            $this->addr = Utils::getLongPrefixFromCIDR($anyFormat);
            $this->mask = Utils::getMaskBitsFromCIDR($anyFormat);
        } else {
            throw new \InvalidArgumentException('Wrong arguments');
        }

        return $this;
    }

    public function numeric(): int
    {
        return $this->addr;
    }

    public function netmask(): int
    {
        return $this->mask;
    }

    public function negativeMask(): int
    {
        return (PHP_INT_SIZE == 8 ? (~$this->mask) & 0x00000000FFFFFFFF : ~$this->mask);
    }

    public function prefixLength(): int
    {
        $bitsCount = 0;

        for ($i=0; $i<$this->maxPrefixLength; $i++) {
            $bitsCount += ($this->negativeMask() >> $i) & 1;
        }

        return $this->maxPrefixLength - $bitsCount;
    }

    public function network(): int
    {
        return $this->addr & $this->mask;
    }

    public function broadcast(): int
    {
        return $this->network() + $this->negativeMask();
    }

    public function cidr(): string
    {
        return Utils::toString($this->addr) . '/' . $this->prefixLength();
    }

    public function range(): string
    {
        return Utils::toString($this->network()) . ' - ' . Utils::toString($this->broadcast());
    }

    public function reverse(): string
    {
        $octets = explode('.', Utils::toString($this->addr));
        return implode('.', array_reverse($octets)) . '.in-addr.arpa';
    }

    public function netType(): string
    {
        for ($i=0; $i<count(Utils::$networkTypes); $i++) {
            if ($this->match(self::create(Utils::$networkTypes[$i]['AddressBlock']))) {
                return Utils::$networkTypes[$i]['PresentUse'];
            }
        }

        return 'Public';
    }

    // TODO: $scope - array of Address objects
    public function match($scope): bool
    {
        if (is_array($scope)) {
            for ($i=0; $i<count($scope); $i++) {
                if ($this->match(self::create($scope[$i]))) {
                    return true;
                }
            }
        } else if ($scope instanceof Address) {
            return
                ($scope->network() <= $this->numeric())
                && ($scope->broadcast() >= $this->numeric());
        } else {
            throw new \InvalidArgumentException('Wrong scope argument');
        }

        return false;
    }

    public function netClass(): string
    {
        if ($this->match(Utils::$privateNetworks)) {
            return 'E';
        } else if ($this->match(Utils::$multicastNetworks)) {
            return 'D';
        } else if ($this->mask >= 0xFFFFFF00) {
            return 'C';
        } else if ($this->mask >= 0xFFFF0000) {
            return 'B';
        } else if ($this->mask >= 0xFF000000) {
            return 'A';
        }
        return '-';
    }
}