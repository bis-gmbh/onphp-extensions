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
    public function numeric(): int;
    public function netmask(): int;
    public function negativeMask(): int;
    public function prefixLength(): int;
    public function network(): int;
    public function broadcast(): int;
    public function numAddrs(): int;
    public function numHosts(): int;
    public function hostBits(): int;
    public function cidr(): string;
    public function range(): string;
    public function reverse(): string;
    public function netType(): string;
    public function match($scope): bool;
}
