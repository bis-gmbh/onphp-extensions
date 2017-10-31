<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

use \Onphp\Extensions\Net\IP\Utils6 as IPUtils;

class IPUtils6Test extends PHPUnit_Framework_TestCase
{
    public function testIsNumeric()
    {
        $this->assertFalse(IPUtils::isNumeric(null));
        $this->assertFalse(IPUtils::isNumeric(false));
        $this->assertFalse(IPUtils::isNumeric(true));
        $this->assertFalse(IPUtils::isNumeric('127.0.0.1'));
        $this->assertFalse(IPUtils::isNumeric(-1));
        $this->assertTrue(IPUtils::isNumeric(0));
        $this->assertTrue(IPUtils::isNumeric(4294967296));
    }

    public function testIsTextual()
    {
        $this->assertFalse(IPUtils::isTextual(null));
        $this->assertFalse(IPUtils::isTextual(''));
        $this->assertFalse(IPUtils::isTextual('a:b:c:d:e:f:g:0'));
        $this->assertTrue(IPUtils::isTextual('fe80:0000:0000:0000:0204:61ff:254.157.241.086')); // hmmm...
        $this->assertTrue(IPUtils::isTextual('1111:2222::5555:6666:7777:8888'));
        $this->assertTrue(IPUtils::isTextual('0:0:0:0:0:FFFF:129.144.52.38'));
        $this->assertTrue(IPUtils::isTextual('0:1:2:3:4:5:6:7'));
        $this->assertTrue(IPUtils::isTextual('1111:2222::'));
        $this->assertTrue(IPUtils::isTextual('255.255.255.255'));
        $this->assertTrue(IPUtils::isTextual('::ffff:2.3.4.0'));
        $this->assertTrue(IPUtils::isTextual('a:aaaa::'));
        $this->assertTrue(IPUtils::isTextual('a::f'));
    }

    public function testIsCIDR()
    {
        $this->assertFalse(IPUtils::isCIDR(null));
        $this->assertFalse(IPUtils::isCIDR(''));
        $this->assertFalse(IPUtils::isCIDR('/'));
        $this->assertFalse(IPUtils::isCIDR('/129'));
        $this->assertFalse(IPUtils::isCIDR('a:b:c:d:e:f:g:0'));
        $this->assertTrue(IPUtils::isCIDR('1111:2222::5555:6666:7777:8888/0'));
        $this->assertTrue(IPUtils::isCIDR('0:0:0:0:0:FFFF:129.144.52.38/64'));
        $this->assertTrue(IPUtils::isCIDR('0:1:2:3:4:5:6:7/128'));
        $this->assertTrue(IPUtils::isCIDR('1111:2222::/74'));
        $this->assertTrue(IPUtils::isCIDR('255.255.255.255/48'));
        $this->assertTrue(IPUtils::isCIDR('::ffff:2.3.4.0/109'));
        $this->assertTrue(IPUtils::isCIDR('a:aaaa::/4'));
        $this->assertTrue(IPUtils::isCIDR('a::f/117'));
    }

    public function testDetectFormat()
    {
        $this->assertEquals(IPUtils::detectFormat(null), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(true), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(false), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(new \StdClass), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(''), 'unknown');
        $this->assertEquals(IPUtils::detectFormat(2130706433), 'numeric');
        $this->assertEquals(IPUtils::detectFormat('::'), 'textual');
        $this->assertEquals(IPUtils::detectFormat('127.0.0.1'), 'textual');
        $this->assertEquals(IPUtils::detectFormat('0:1:2:3:4:5:6:7/128'), 'cidr');
    }

    public function testGetNumericPrefixFromCIDR()
    {
        $this->assertEquals(gmp_cmp(IPUtils::getNumericPrefixFromCIDR('127.0.0.1/8'), gmp_init(2130706433)), 0);
        $this->assertEquals(gmp_cmp(IPUtils::getNumericPrefixFromCIDR('ffff::/8'), gmp_init('0xFFFF0000000000000000000000000000')), 0);

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPUtils::getNumericPrefixFromCIDR('127.0.0.1'), 2130706433);
    }

    public function testGetMaskBitsFromCIDR()
    {
        $this->assertEquals(gmp_cmp(IPUtils::getMaskBitsFromCIDR('::127.0.0.1/0'), gmp_init(0)), 0);
        $this->assertEquals(gmp_cmp(IPUtils::getMaskBitsFromCIDR('::127.0.0.1/32'), gmp_init('0xFFFFFFFF000000000000000000000000')), 0);
        $this->assertEquals(gmp_cmp(IPUtils::getMaskBitsFromCIDR('::127.0.0.1/64'), gmp_init('0xFFFFFFFFFFFFFFFF0000000000000000')), 0);
        $this->assertEquals(gmp_cmp(IPUtils::getMaskBitsFromCIDR('::127.0.0.1/128'), gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF')), 0);

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPUtils::getMaskBitsFromCIDR('127.0.0.1'), 8);
    }

    public function testToNumeric()
    {
        $this->assertEquals(gmp_cmp(IPUtils::toNumeric('::'), gmp_init(0)), 0);
        $this->assertEquals(gmp_cmp(IPUtils::toNumeric('::127.0.0.1'), gmp_init(2130706433)), 0);
        $this->assertEquals(gmp_cmp(IPUtils::toNumeric('::255.255.255.255'), gmp_init(4294967295)), 0);

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPUtils::toNumeric(''), 0);
        $this->assertEquals(IPUtils::toNumeric('127.0.0.1/8'), 2130706433);
    }

    public function testToTextual()
    {
        $this->assertEquals(IPUtils::toTextual(gmp_init(3325256815)), '::198.51.100.111');
        $this->assertEquals(IPUtils::toTextual(gmp_init(0)), '::');
        $this->assertEquals(IPUtils::toTextual(gmp_init(4294967295)), '::255.255.255.255');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPUtils::toTextual(new \StdClass), '255.255.255.255');
    }

    public function testToBinaryString()
    {
        $this->assertEquals(IPUtils::toBinaryString(gmp_init(0)), "00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000");
        $this->assertEquals(IPUtils::toBinaryString(gmp_init(3325256815)), '00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011000110001100110110010001101111');
        $this->assertNotEquals(IPUtils::toBinaryString(gmp_init(3325256815)), '00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001000110001100110110010001101111');
        $this->assertEquals(IPUtils::toBinaryString(gmp_init(4294967295)), '00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111111111111111111111111111111');
        $this->assertNotEquals(IPUtils::toBinaryString(gmp_init(4294967294)), '00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111111111111111111111111111111');
    }
}
