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
    public static function version(): int;
    public static function create($anyFormat = null, $mask = null): Address;
    public function assign($anyFormat, $mask = null);
    public function numeric(): int;
    public function netmask(): int;
    public function reverseMasc(): int;
    public function maskBits(): int;
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
