<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

use \Onphp\Extensions\Net\IP\Utils as IPUtils;

class IPUtilsTest extends PHPUnit_Framework_TestCase
{
    public function testIsLong()
    {
        $this->assertEquals(IPUtils::isLong(null), false);
        $this->assertEquals(IPUtils::isLong(false), false);
        $this->assertEquals(IPUtils::isLong(true), false);
        $this->assertEquals(IPUtils::isLong('127.0.0.1'), false);
        $this->assertEquals(IPUtils::isLong(-1), false);
        $this->assertEquals(IPUtils::isLong(4294967296), false);
        $this->assertEquals(IPUtils::isLong(0), true);
        $this->assertEquals(IPUtils::isLong(2130706433), true);
    }

    public function testIsString()
    {
        $this->assertEquals(IPUtils::isString(null), false);
        $this->assertEquals(IPUtils::isString(''), false);
        $this->assertEquals(IPUtils::isString('300'), false);
        $this->assertEquals(IPUtils::isString('127.0.256.1'), false);
        $this->assertEquals(IPUtils::isString('0.0.0.0'), true);
        $this->assertEquals(IPUtils::isString('127.0.0.1'), true);
        $this->assertEquals(IPUtils::isString('10.0.100.1'), true);
        $this->assertEquals(IPUtils::isString('224.1.0.0'), true);
        $this->assertEquals(IPUtils::isString('255.255.255.255'), true);
        $this->assertEquals(IPUtils::isString('169.255.255'), true);
        $this->assertEquals(IPUtils::isString('169.255'), true);
        $this->assertEquals(IPUtils::isString('169'), true);
    }

    public function testIsCIDR()
    {
        $this->assertEquals(IPUtils::isCIDR(null), false);
        $this->assertEquals(IPUtils::isCIDR(''), false);
        $this->assertEquals(IPUtils::isCIDR('/'), false);
        $this->assertEquals(IPUtils::isCIDR('127.0.0.1'), false);
        $this->assertEquals(IPUtils::isCIDR('0.0.400.0/0'), false);
        $this->assertEquals(IPUtils::isCIDR('127.0.0.1/33'), false);
        $this->assertEquals(IPUtils::isCIDR('192.168.100.2/'), false);
        $this->assertEquals(IPUtils::isCIDR('/30'), false);
        $this->assertEquals(IPUtils::isCIDR('0.0.0.0/0'), true);
        $this->assertEquals(IPUtils::isCIDR('192.168.100.2/30'), true);
        $this->assertEquals(IPUtils::isCIDR('192.168.100/24'), true);
        $this->assertEquals(IPUtils::isCIDR('192.168/16'), true);
        $this->assertEquals(IPUtils::isCIDR('10/8'), true);
        $this->assertEquals(IPUtils::isCIDR('255.255.255.255/32'), true);
    }

    public function testDetectFormat()
    {
        $this->assertEquals(IPUtils::detectFormat(null), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(true), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(false), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(new \StdClass), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(''), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(2130706433), 'numeric');
        $this->assertEquals(IPUtils::detectFormat('127.0.0.1'), 'string');
        $this->assertEquals(IPUtils::detectFormat('192.168.100.2/30'), 'cidr');
    }

    public function testGetLongPrefixFromCIDR()
    {
        $this->assertEquals(IPUtils::getLongPrefixFromCIDR('127.0.0.1/8'), 2130706433);

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPUtils::getLongPrefixFromCIDR('127.0.0.1'), 2130706433);
    }

    public function testGetMaskBitsFromCIDR()
    {
        $this->assertEquals(IPUtils::getMaskBitsFromCIDR('127.0.0.1/8'), 16777215);

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPUtils::getMaskBitsFromCIDR('127.0.0.1'), 8);
    }

    public function testToLong()
    {
        $this->assertEquals(IPUtils::toLong('0.0.0.0'), 0);
        $this->assertEquals(IPUtils::toLong('127.0.0.1'), 2130706433);
        $this->assertEquals(IPUtils::toLong('255.255.255.255'), 4294967295);

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPUtils::toLong(''), 0);
        $this->assertEquals(IPUtils::toLong('127.0.0.1/8'), 2130706433);
    }

    public function testToString()
    {
        $this->assertEquals(IPUtils::toString(3325256815), '198.51.100.111');
        $this->assertEquals(IPUtils::toString(0), '0.0.0.0');
        $this->assertEquals(IPUtils::toString(4294967295), '255.255.255.255');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPUtils::toString(4294967296), '255.255.255.255');
        $this->assertEquals(IPUtils::toString(3325256815), '198.51.100.111/8');
    }

    public function testToBinaryString()
    {
        $this->assertEquals(IPUtils::toBinaryString(0), "00000000000000000000000000000000");
        $this->assertEquals(IPUtils::toBinaryString(3325256815), '11000110001100110110010001101111');
        $this->assertNotEquals(IPUtils::toBinaryString(3325256815), '01000110001100110110010001101111');
        $this->assertEquals(IPUtils::toBinaryString(4294967295), '11111111111111111111111111111111');
        $this->assertNotEquals(IPUtils::toBinaryString(4294967294), '11111111111111111111111111111111');

        $this->expectException('OutOfRangeException');
        $this->assertEquals(IPUtils::toBinaryString(-1), '');
        $this->assertEquals(IPUtils::toBinaryString(4294967296), '');
    }
}
