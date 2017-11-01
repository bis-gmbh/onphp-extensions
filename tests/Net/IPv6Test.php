<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

use \Onphp\Extensions\Net\IP\v6 as IPv6;

class IPv6Test extends PHPUnit_Framework_TestCase
{
    public $invalidTextualAddresses = [];

    public function setup()
    {
        $this->invalidTextualAddresses = require 'data/ipv6-invalid.php';
    }

    public function testAssign()
    {
        $ip = new IPv6;

        $ip->assign(0);
        $ip->assign(0xFFFFFFFF);
        $ip->assign('::192.168.0.1', '::255.255.255.0');
        $ip->assign('::10/8');

        $this->expectException('InvalidArgumentException');
        $ip->assign(-1);
        $ip->assign('192.168.0.1', 0xFFFFFF00);
        $ip->assign('240/4', '255');
    }

    public function testToNumeric()
    {
        $this->assertEquals(IPv6::toNumeric('::'), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::toNumeric('::127.0.0.1'), '0x0000000000000000000000007f000001');
        $this->assertEquals(IPv6::toNumeric('::255.255.255.255'), '0x000000000000000000000000ffffffff');
        $this->assertEquals(IPv6::toNumeric('ffff::'), '0xffff0000000000000000000000000000');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPv6::toNumeric(''), '0');
        $this->assertEquals(IPv6::toNumeric('127.0.0.1/8'), '2130706433');
    }

    public function testToTextual()
    {
        $this->assertEquals(IPv6::toTextual(3325256815), '::198.51.100.111');
        $this->assertEquals(IPv6::toTextual(0), '::');
        $this->assertEquals(IPv6::toTextual('4294967295'), '::255.255.255.255');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPv6::toTextual(new \StdClass), '255.255.255.255');
    }

    public function testToBinaryString()
    {
        $this->assertEquals(IPv6::toBinaryString(0), "0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000");
        $this->assertEquals(IPv6::toBinaryString(3325256815), '0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011000110001100110110010001101111');
        $this->assertNotEquals(IPv6::toBinaryString(3325256815), '0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001000110001100110110010001101111');
        $this->assertEquals(IPv6::toBinaryString('4294967295'), '0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111111111111111111111111111111');
        $this->assertNotEquals(IPv6::toBinaryString(4294967294), '0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111111111111111111111111111111');
    }

    public function testNumeric()
    {
        $this->assertEquals(IPv6::create(0)->numeric(), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::create(0xFFFFFFFF)->numeric(), '0x000000000000000000000000ffffffff');
        $this->assertEquals(IPv6::create('::0.0.0.0')->numeric(), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::create('::255.255.255.255')->numeric(), '0x000000000000000000000000ffffffff');
        $this->assertEquals(IPv6::create('::127.0.0.1')->numeric(), '0x0000000000000000000000007f000001');
        $this->assertEquals(IPv6::create('0:ffff::')->numeric(), '0x0000ffff000000000000000000000000');
        $this->assertEquals(IPv6::create('ffff::')->numeric(), '0xffff0000000000000000000000000000');
    }

    public function testNetmask()
    {
        $this->assertEquals(IPv6::create(0)->netmask(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('::0.0.0.0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->netmask(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff::')->netmask(), '0xffffffffffffffffffffffff00000000');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff::')->netmask(), '0xffffffffffffffff0000000000000000');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff::')->netmask(), '0xffffffff000000000000000000000000');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff::')->netmask(), '0xffffffffffffffffffffffffffff0000');
        $this->assertEquals(IPv6::create('::0.0.0.0/126')->netmask(), '0xfffffffffffffffffffffffffffffffc');

        // arbitrary masks are allowed, but their text representations will be incorrect
        $this->assertEquals(IPv6::create('::0.0.0.0', '0:ffff:0:0:f::')->netmask(), '0x0000ffff00000000000f000000000000');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPv6::create('::0.0.0.0', 0)->netmask(), '0x00000000000000000000000000000000');
    }

    public function testNegativeMask()
    {
        $this->assertEquals(IPv6::create(0)->negativeMask(), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::create('::0.0.0.0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->negativeMask(), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff::')->negativeMask(), '0x000000000000000000000000ffffffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff::')->negativeMask(), '0x0000000000000000ffffffffffffffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff::')->negativeMask(), '0x00000000ffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff::')->negativeMask(), '0x0000000000000000000000000000ffff');
        $this->assertEquals(IPv6::create('::0.0.0.0/126')->negativeMask(), '0x00000000000000000000000000000003');

        $this->assertEquals(IPv6::create('::0.0.0.0', '0:ffff:0:0:f::')->negativeMask(), '0xffff0000fffffffffff0ffffffffffff');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPv6::create('::0.0.0.0', 0)->negativeMask(), '0x00000000000000000000000000000000');
    }

    public function testPrefixLength()
    {
        $this->assertEquals(IPv6::create(0)->prefixLength(), 128);
        $this->assertEquals(IPv6::create('::0.0.0.0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->prefixLength(), 128);
        $this->assertEquals(IPv6::create('::0.0.0.0', '::')->prefixLength(), 0);
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff::')->prefixLength(), 96);
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff::')->prefixLength(), 64);
        $this->assertEquals(IPv6::create('::', 'ffff:ffff::')->prefixLength(), 32);
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff::')->prefixLength(), 112);
        $this->assertEquals(IPv6::create('::0.0.0.0/126')->prefixLength(), 126);

        $this->assertEquals(IPv6::create('::0.0.0.0', '0:ffff:0:0:f::')->prefixLength(), 20);
    }

    public function testFirst()
    {
        $this->assertEquals(IPv6::create('::/0')->first()->numeric(), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->first()->numeric(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->first()->numeric(), '0xffffffffffffffff0000000000000000');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->first()->numeric(), '0x2a0206b8000000000000000000000000');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->first()->numeric(), '0x20000000000000000000000000000000');
    }

    public function testLast()
    {
        $this->assertEquals(IPv6::create('::/0')->last()->numeric(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->last()->numeric(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->last()->numeric(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->last()->numeric(), '0x2a0206bbffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->last()->numeric(), '0x2fffffffffffffffffffffffffffffff');
    }

    public function testNumAddrs()
    {
        $this->assertEquals(IPv6::create('::/0')->numAddrs(), '340282366920938463463374607431768211455');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->numAddrs(), '1');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->numAddrs(), '18446744073709551616');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->numAddrs(), '316912650057057350374175801344');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->numAddrs(), '21267647932558653966460912964485513216');
    }

    public function testNumHosts()
    {
        $this->assertEquals(IPv6::create('::/0')->numHosts(), '340282366920938463463374607431768211453');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->numHosts(), '1');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->numHosts(), '18446744073709551614');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->numHosts(), '316912650057057350374175801342');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->numHosts(), '21267647932558653966460912964485513214');
    }

    public function testHostBits()
    {
        $this->assertEquals(IPv6::create('::/0')->hostBits(), 128);
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->hostBits(), 0);
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->hostBits(), 64);
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->hostBits(), 98);
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->hostBits(), 124);
    }

    public function testAddr()
    {
        $this->assertEquals(IPv6::create('::/0')->addr(), '::');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->addr(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->addr(), 'ffff:ffff:ffff:ffff::');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->addr(), '2a02:6b8::2:242');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->addr(), '2a00:1450:4010:c0f::64');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64:1.2.3.4/4')->addr(), '2a00:1450:4010:c0f::64:102:304');
    }

    public function testMask()
    {
        $this->assertEquals(IPv6::create('::/0')->mask(), '::');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->mask(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->mask(), 'ffff:ffff:ffff:ffff::');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->mask(), 'ffff:fffc::');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->mask(), 'f000::');
    }

    public function testCidr()
    {
        $this->assertEquals(IPv6::create('::/0')->cidr(), '::/0');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->cidr(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->cidr(), 'ffff:ffff:ffff:ffff::/64');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->cidr(), '2a02:6b8::2:242/30');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->cidr(), '2a00:1450:4010:c0f::64/4');
    }

    public function testReverse()
    {
        $this->assertEquals(IPv6::create('::/0')->reverse(), '::.ip6.arpa.');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->reverse(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff.ip6.arpa.');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->reverse(), '::ffff:ffff:ffff:ffff.ip6.arpa.');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->reverse(), '242:2::6b8:2a02.ip6.arpa.');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->reverse(), '64::c0f:4010:1450:2a00.ip6.arpa.');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64:1.2.3.4/4')->reverse(), '304:102:64::c0f:4010:1450:2a00.ip6.arpa.');
    }

    public function testNetType()
    {
        $this->assertEquals(IPv6::create()->netType(), 'Unspecified');
        $this->assertEquals(IPv6::create('::1')->netType(), 'Loopback');
        $this->assertEquals(IPv6::create('FF00::1')->netType(), 'Multicast');
        $this->assertEquals(IPv6::create('FE80::1')->netType(), 'Link-Local unicast');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242')->netType(), 'Global Unicast');
    }

    public function testFull()
    {
        $this->assertEquals(IPv6::create('::')->full(), '0000:0000:0000:0000:0000:0000:0000:0000');
        $this->assertEquals(IPv6::create('::1')->full(), '0000:0000:0000:0000:0000:0000:0000:0001');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242')->full(), '2a02:06b8:0000:0000:0000:0000:0002:0242');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64:1.2.3.4')->full(), '2a00:1450:4010:0c0f:0000:0064:0102:0304');
    }

    public function testFull4()
    {
        $this->assertEquals(IPv6::create('::')->full4(), '0000:0000:0000:0000:0000:0000:0.0.0.0');
        $this->assertEquals(IPv6::create('::1')->full4(), '0000:0000:0000:0000:0000:0000:0.0.0.1');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242')->full4(), '2a02:06b8:0000:0000:0000:0000:0.2.2.66');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64:1.2.3.4')->full4(), '2a00:1450:4010:0c0f:0000:0064:1.2.3.4');
    }

    public function testCompressed()
    {
        $this->assertEquals(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0000')->compressed(), '::');
        $this->assertEquals(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0001')->compressed(), '::1');
        $this->assertEquals(IPv6::create('0000:0000:000f:000f:0000:0000:000f:0001')->compressed(), '::f:f:0:0:f:1');
        $this->assertEquals(IPv6::create('000f:0000:000f:000f:0000:0000:0000:0000')->compressed(), 'f:0:f:f::');
        $this->assertEquals(IPv6::create('000f:0000:000f:000f:0000:0000:000f:0001')->compressed(), 'f:0:f:f::f:1');
        $this->assertEquals(IPv6::create('2a02:06b8:0000:0000:0000:0000:0002:0242')->compressed(), '2a02:6b8::2:242');
        $this->assertEquals(IPv6::create('2a00:1450:4010:0c0f:0000:0064:0102:0304')->compressed(), '2a00:1450:4010:c0f::64:102:304');
    }

    public function testCompressed4()
    {
        $this->assertEquals(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0000')->compressed4(), '::0.0.0.0');
        $this->assertEquals(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0001')->compressed4(), '::0.0.0.1');
        $this->assertEquals(IPv6::create('000f:0000:000f:000f:0000:0000:000f:0001')->compressed4(), 'f:0:f:f::0.15.0.1');
        $this->assertEquals(IPv6::create('2a02:06b8:0000:0000:0000:0000:0002:0242')->compressed4(), '2a02:6b8::0.2.2.66');
        $this->assertEquals(IPv6::create('2a00:1450:4010:0c0f:0000:0064:0102:0304')->compressed4(), '2a00:1450:4010:c0f::64:1.2.3.4');
    }

    public function testContains()
    {
        $this->assertTrue(IPv6::create()->contains(IPv6::create()));
        $this->assertTrue(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->contains(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')));
        $this->assertTrue(IPv6::create('2a02:6b8:f::')->contains(IPv6::create('2a02:6b8::2:242/4')));
        $this->assertFalse(IPv6::create('2a02:6b7::')->contains(IPv6::create('2a02:6b8::2:242/32')));
    }

    public function testWithin()
    {
        $this->assertTrue(IPv6::create()->within(IPv6::create()));
        $this->assertTrue(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->within(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')));
        $this->assertTrue(IPv6::create('2a02:6b8::2:242/4')->within(IPv6::create('2a02:6b8:f::')));
        $this->assertFalse(IPv6::create('2a02:6b8::2:242/32')->within(IPv6::create('2a02:6b7::')));
    }

    public function testToString()
    {
        $ip = new IPv6('::/0');

        $this->assertEquals(sprintf("%s", $ip), '::/0');

        $ip->assign('2a02:06b8:0000:0000:0000:0000:0002:0242/4');

        $this->assertEquals(sprintf("%s", $ip), '2a02:6b8::2:242/4');
    }
}
