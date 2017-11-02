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
    /**
     * @return int
     */
    public function version();

    /**
     * @param mixed $anyFormat
     * @param mixed $mask
     * @return Address
     */
    public static function create($anyFormat = null, $mask = null);

    /**
     * @param mixed $anyFormat
     * @param mixed $mask
     * @return Address
     */
    public function assign($anyFormat, $mask = null);

    /**
     * @return string binary value
     */
    public function binary();

    /**
     * @return int|string numeric value depend on ip version
     */
    public function decimal();

    /**
     * @return string numeric value
     */
    public function hexadecimal();

    /**
     * @return string hexadecimal value
     */
    public function netmask();

    /**
     * @return int
     */
    public function prefixLength();

    /**
     * @return Address
     */
    public function first();

    /**
     * @return Address
     */
    public function last();

    /**
     * @return int
     */
    public function numAddrs();

    /**
     * @return int
     */
    public function numHosts();

    /**
     * @return int
     */
    public function hostBits();

    /**
     * @param $scope
     * @return bool
     */
    public function contains($scope);

    /**
     * @param Address $addr
     * @return bool
     */
    public function within(Address $addr);

    /**
     * @return string
     */
    public function addr();

    /**
     * @return string
     */
    public function mask();

    /**
     * @return string
     */
    public function cidr();

    /**
     * @return string
     */
    public function range();

    /**
     * @return string
     */
    public function reverse();

    /**
     * @return string
     */
    public function reverseMask();

    /**
     * @return string
     */
    public function netType();
}
