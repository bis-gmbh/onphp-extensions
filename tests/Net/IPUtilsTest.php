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
        $this->assertFalse(IPUtils::isLong(null));
        $this->assertFalse(IPUtils::isLong(false));
        $this->assertFalse(IPUtils::isLong(true));
        $this->assertFalse(IPUtils::isLong('127.0.0.1'));
        $this->assertFalse(IPUtils::isLong(-1));
        $this->assertFalse(IPUtils::isLong(4294967296));
        $this->assertTrue(IPUtils::isLong(0));
        $this->assertTrue(IPUtils::isLong(2130706433));
    }

    public function testIsString()
    {
        $this->assertFalse(IPUtils::isString(null));
        $this->assertFalse(IPUtils::isString(''));
        $this->assertFalse(IPUtils::isString('300'));
        $this->assertFalse(IPUtils::isString('127.0.256.1'));
        $this->assertTrue(IPUtils::isString('0.0.0.0'));
        $this->assertTrue(IPUtils::isString('127.0.0.1'));
        $this->assertTrue(IPUtils::isString('10.0.100.1'));
        $this->assertTrue(IPUtils::isString('224.1.0.0'));
        $this->assertTrue(IPUtils::isString('255.255.255.255'));
        $this->assertTrue(IPUtils::isString('169.255.255'));
        $this->assertTrue(IPUtils::isString('169.255'));
        $this->assertTrue(IPUtils::isString('169'));
    }

    public function testIsCIDR()
    {
        $this->assertFalse(IPUtils::isCIDR(null));
        $this->assertFalse(IPUtils::isCIDR(''));
        $this->assertFalse(IPUtils::isCIDR('/'));
        $this->assertFalse(IPUtils::isCIDR('127.0.0.1'));
        $this->assertFalse(IPUtils::isCIDR('0.0.400.0/0'));
        $this->assertFalse(IPUtils::isCIDR('127.0.0.1/33'));
        $this->assertFalse(IPUtils::isCIDR('192.168.100.2/'));
        $this->assertFalse(IPUtils::isCIDR('/30'));
        $this->assertTrue(IPUtils::isCIDR('0.0.0.0/0'));
        $this->assertTrue(IPUtils::isCIDR('192.168.100.2/30'));
        $this->assertTrue(IPUtils::isCIDR('192.168.100/24'));
        $this->assertTrue(IPUtils::isCIDR('192.168/16'));
        $this->assertTrue(IPUtils::isCIDR('10/8'));
        $this->assertTrue(IPUtils::isCIDR('255.255.255.255/32'));
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
        $this->assertEquals(IPUtils::getMaskBitsFromCIDR('127.0.0.1/0'), 0);
        $this->assertEquals(IPUtils::getMaskBitsFromCIDR('127.0.0.1/8'), 0xFF000000);
        $this->assertEquals(IPUtils::getMaskBitsFromCIDR('127.0.0.1/24'), 0xFFFFFF00);
        $this->assertEquals(IPUtils::getMaskBitsFromCIDR('127.0.0.1/32'), 0xFFFFFFFF);

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
