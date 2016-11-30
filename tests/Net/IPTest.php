<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

use \Onphp\Extensions\Net\IP;

class IPTest extends PHPUnit_Framework_TestCase
{
    public function testGetRangeByCIDR()
    {
        // check failure cases
        $range = IP::getRangeByCIDR(null);
        $this->assertEquals($range, false);

        $range = IP::getRangeByCIDR('anything');
        $this->assertEquals($range, false);

        $range = IP::getRangeByCIDR('45.12.355.5/8');
        $this->assertEquals($range, false);

        $range = IP::getRangeByCIDR('1.2.3.4/35');
        $this->assertEquals($range, false);

        // check succeed cases
        list($n, $b) = IP::getRangeByCIDR('10.0.0.1/0');
        $this->assertEquals($n . ' - ' . $b, '0.0.0.0 - 255.255.255.255');

        list($n, $b) = IP::getRangeByCIDR('192.168.0.1/24');
        $this->assertEquals($n . ' - ' . $b, '192.168.0.0 - 192.168.0.255');

        list($n, $b) = IP::getRangeByCIDR('85.239.177.234/30');
        $this->assertEquals($n . ' - ' . $b, '85.239.177.232 - 85.239.177.235');

        list($n, $b) = IP::getRangeByCIDR('8.8.4.4/32');
        $this->assertEquals($n . ' - ' . $b, '8.8.4.4 - 8.8.4.4');
    }
}
