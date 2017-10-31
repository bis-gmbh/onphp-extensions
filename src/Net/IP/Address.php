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
     * @return mixed numeric value depend on ip version
     */
    public function numeric();

    /**
     * @return mixed numeric value depend on ip version
     */
    public function netmask();

    /**
     * @return mixed numeric value depend on ip version
     */
    public function negativeMask();

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
     * @param Address $addr
     * @return bool
     */
    public function ltEq(Address $addr);

    /**
     * @param Address $addr
     * @return bool
     */
    public function gtEq(Address $addr);

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
    public function netType();
}
