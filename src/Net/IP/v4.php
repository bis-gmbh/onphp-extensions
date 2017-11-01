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

    public function assign($anyFormat, $maskString = null)
    {
        if (Utils::detectFormat($anyFormat) === 'numeric') {
            if ($maskString !== null) {
                throw new \InvalidArgumentException('Mask argument not allowed');
            }
            $this->addr = $anyFormat;
        } else if (Utils::detectFormat($anyFormat) === 'string') {
            if ($maskString !== null) {
                if (Utils::detectFormat($maskString) === 'string') {
                    $this->mask = Utils::toLong($maskString);
                } else {
                    throw new \InvalidArgumentException('Mask argument must have string format');
                }
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

    public function numeric()
    {
        return $this->addr;
    }

    public function netmask()
    {
        return $this->mask;
    }

    public function negativeMask()
    {
        return (PHP_INT_SIZE == 8 ? (~$this->mask) & 0x00000000FFFFFFFF : ~$this->mask);
    }

    public function prefixLength()
    {
        $bitsCount = 0;

        for ($i=0; $i<$this->maxPrefixLength; $i++) {
            $bitsCount += ($this->negativeMask() >> $i) & 1;
        }

        return $this->maxPrefixLength - $bitsCount;
    }

    public function first()
    {
        return new self($this->addr & $this->mask);
    }

    public function last()
    {
        return new self(($this->addr & $this->mask) + $this->negativeMask());
    }

    public function ltEq(Address $addr)
    {
        return $this->addr <= $addr->numeric();
    }

    public function gtEq(Address $addr)
    {
        return $this->addr >= $addr->numeric();
    }

    public function addr()
    {
        return Utils::toString($this->addr);
    }

    public function mask()
    {
        return Utils::toString($this->mask);
    }

    public function cidr()
    {
        return Utils::toString($this->addr) . '/' . $this->prefixLength();
    }

    public function range()
    {
        return $this->network() . ' - ' . $this->broadcast();
    }

    public function reverse()
    {
        $octets = explode('.', Utils::toString($this->addr));
        return implode('.', array_reverse($octets)) . '.in-addr.arpa.';
    }

    public function netType()
    {
        for ($i=0; $i<count(Utils::$networkTypes); $i++) {
            if ($this->contains(self::create(Utils::$networkTypes[$i]['AddressBlock']))) {
                return Utils::$networkTypes[$i]['PresentUse'];
            }
        }

        return 'Public';
    }

    /**
     * @return Address|v4
     */
    public function network()
    {
        return $this->first();
    }

    /**
     * @return Address|v4
     */
    public function broadcast()
    {
        return $this->last();
    }

    /**
     * @return string
     */
    public function netClass()
    {
        if ($this->contains(Utils::$privateNetworks)) {
            return 'E';
        } else if ($this->contains(Utils::$multicastNetworks)) {
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
