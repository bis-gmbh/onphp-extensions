<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\IP;

abstract class BaseAddress implements Address
{
    protected $version;
    protected $addr;
    protected $mask;
    protected $maxPrefixLength;

    abstract public function assign($anyFormat, $mask = null): Address;
    abstract public function numeric(): int;
    abstract public function netmask(): int;
    abstract public function negativeMask(): int;
    abstract public function prefixLength(): int;
    abstract public function network(): int;
    abstract public function broadcast(): int;
    abstract public function cidr(): string;
    abstract public function range(): string;
    abstract public function reverse(): string;
    abstract public function netType(): string;
    abstract public function match($scope): bool;

    public function version(): int
    {
        return $this->version;
    }

    public static function create($anyFormat = null, $mask = null): Address
    {
        return new static($anyFormat, $mask);
    }

    public function numAddrs(): int
    {
        $prefixLength = $this->prefixLength();

        if ($prefixLength === $this->maxPrefixLength) {
            return 1;
        } else if ($prefixLength === 0) {
            return $this->negativeMask();
        }

        return $this->broadcast() - $this->network() + 1;
    }

    public function numHosts(): int
    {
        $num = $this->numAddrs();
        return ($num > 2) ? ($num - 2) : 1;
    }

    public function hostBits(): int
    {
        return $this->maxPrefixLength - $this->prefixLength();
    }

    public function __toString(): string
    {
        return $this->cidr();
    }
}
