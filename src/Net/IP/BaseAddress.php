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

    public function version()
    {
        return $this->version;
    }

    public static function create($anyFormat = null, $mask = null)
    {
        return new static($anyFormat, $mask);
    }

    public function hostBits(): int
    {
        return $this->maxPrefixLength - $this->prefixLength();
    }

    public function contains($scope)
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

    public function within(Address $addr)
    {
        return $addr->first()->gtEq($this->first()) && $addr->last()->ltEq($this->last());
    }

    public function __toString()
    {
        return $this->cidr();
    }
}
