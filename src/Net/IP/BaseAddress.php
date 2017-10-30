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

    abstract public function ltEq(Address $value): bool;
    abstract public function gtEq(Address $value): bool;

    abstract public function addr(): string;
    abstract public function mask(): string;
    abstract public function cidr(): string;
    abstract public function range(): string;
    abstract public function reverse(): string;
    abstract public function netType(): string;

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
        } else {
            return $this->negativeMask() + 1;
        }
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

    public function contains($scope): bool
    {
        if (is_array($scope)) {
            for ($i=0; $i<count($scope); $i++) {
                if (
                    $scope[$i] instanceof Address
                    && $this->contains($scope[$i])
                ) {
                    return true;
                } else if ($this->contains(self::create($scope[$i]))) {
                    return true;
                }
            }
        } else if ($scope instanceof Address) {
            return $this->gtEq($scope->first()) && $this->ltEq($scope->last());
        } else {
            throw new \InvalidArgumentException('Wrong scope argument');
        }

        return false;
    }

    public function within(Address $addr): bool
    {
        return $addr->first()->gtEq($this->first()) && $addr->last()->ltEq($this->last());
    }

    public function __toString(): string
    {
        return $this->cidr();
    }
}
