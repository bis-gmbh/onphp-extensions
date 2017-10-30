<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\IP;

interface Address
{
    public function version(): int;

    public static function create($anyFormat = null, $mask = null): Address;
    public function assign($anyFormat, $mask = null): Address;

    public function numeric();
    public function netmask();
    public function negativeMask();

    public function prefixLength(): int;

    public function first(): Address;
    public function last(): Address;

    public function numAddrs(): int;
    public function numHosts(): int;
    public function hostBits(): int;

    public function ltEq(Address $addr): bool;
    public function gtEq(Address $addr): bool;

    public function contains($scope): bool;
    public function within(Address $addr): bool;

    public function addr(): string;
    public function mask(): string;
    public function cidr(): string;
    public function range(): string;
    public function reverse(): string;
    public function netType(): string;
}
