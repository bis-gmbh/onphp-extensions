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
}
