<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

use \Onphp\Extensions\Net\IP\v4 as IPv4;
use \Onphp\Extensions\Net\IP\Utils;

class IPv4Test extends PHPUnit_Framework_TestCase
{
    public function testAssign()
    {
        $ip = new IPv4;

        $ip->assign(0);
        $ip->assign(0xFFFFFFFF);
        $ip->assign('192.168.0.1', '255.255.255.0');
        $ip->assign('10/8');

        $this->expectException('InvalidArgumentException');
        $ip->assign(-1);
        $ip->assign('192.168.0.1', 0xFFFFFF00);
        $ip->assign('240/4', '255');
    }

    public function testNumeric()
    {
        $this->assertEquals(IPv4::create(0)->numeric(), 0);
        $this->assertEquals(IPv4::create(0xFFFFFFFF)->numeric(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('0.0.0.0')->numeric(), 0);
        $this->assertEquals(IPv4::create('255.255.255.255')->numeric(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('127.0.0.1')->numeric(), 2130706433);
    }

    public function testNetmask()
    {
        $this->assertEquals(IPv4::create(0)->netmask(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('0.0.0.0', '255.255.255.255')->netmask(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('0.0.0.0', '0.0.0.0')->netmask(), 0);
        $this->assertEquals(IPv4::create('0', '255.255.255.0')->netmask(), 0xFFFFFF00);
        $this->assertEquals(IPv4::create('0', '255.255.0.0')->netmask(), 0xFFFF0000);
        $this->assertEquals(IPv4::create('0', '255')->netmask(), 0xFF000000);
        $this->assertEquals(IPv4::create('0', '255.255.255.252')->netmask(), 0xFFFFFFFC);
        $this->assertEquals(IPv4::create('0.0.0.0/30')->netmask(), 0xFFFFFFFC);

        // arbitrary masks are allowed, but their text representations will be incorrect
        $this->assertEquals(IPv4::create('0.0.0.0', '255.0.13.187')->netmask(), 0xFF000DBB);
    }

    public function testReverseMask()
    {
        $this->assertEquals(IPv4::create(0)->negativeMask(), 0);
        $this->assertEquals(IPv4::create('0.0.0.0', '255.255.255.255')->negativeMask(), 0);
        $this->assertEquals(IPv4::create('0.0.0.0', '0.0.0.0')->negativeMask(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('0', '255.255.255.0')->negativeMask(), 0x000000FF);
        $this->assertEquals(IPv4::create('0', '255.255.0.0')->negativeMask(), 0x0000FFFF);
        $this->assertEquals(IPv4::create('0', '255')->negativeMask(), 0x00FFFFFF);
        $this->assertEquals(IPv4::create('0', '255.255.255.252')->negativeMask(), 0x00000003);
        $this->assertEquals(IPv4::create('0.0.0.0/30')->negativeMask(), 0x00000003);

        $this->assertEquals(IPv4::create('0.0.0.0', '255.0.13.187')->negativeMask(), 0x00FFF244);
    }

    public function testMaskBits()
    {
        $this->assertEquals(IPv4::create(0)->prefixLength(), 32);
        $this->assertEquals(IPv4::create('0.0.0.0', '255.255.255.255')->prefixLength(), 32);
        $this->assertEquals(IPv4::create('0.0.0.0', '0.0.0.0')->prefixLength(), 0);
        $this->assertEquals(IPv4::create('0', '255.255.255.0')->prefixLength(), 24);
        $this->assertEquals(IPv4::create('0', '255.255.0.0')->prefixLength(), 16);
        $this->assertEquals(IPv4::create('0', '255')->prefixLength(), 8);
        $this->assertEquals(IPv4::create('0', '255.255.255.252')->prefixLength(), 30);
        $this->assertEquals(IPv4::create('0.0.0.0/30')->prefixLength(), 30);

        $this->assertEquals(IPv4::create('0.0.0.0', '255.0.13.187')->prefixLength(), 17);
    }

    public function testNetwork()
    {
        $this->assertEquals(IPv4::create('0/0')->network(), 0);
        $this->assertEquals(IPv4::create('255.255.255.255/32')->network(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('192.168/16')->network(), 3232235520); // 192.168.0.0
        $this->assertEquals(IPv4::create('192.168.100.15/30')->network(), 3232261132); // 192.168.100.12
        $this->assertEquals(IPv4::create('192.168.100.15/4')->network(), 3221225472); // 192.0.0.0
    }

    public function testBroadcast()
    {
        $this->assertEquals(IPv4::create('0/0')->broadcast(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('255.255.255.255/32')->broadcast(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('192.168/16')->broadcast(), 3232301055); // 192.168.255.255
        $this->assertEquals(IPv4::create('192.168.100.15/30')->broadcast(), 3232261135); // 192.168.100.15
        $this->assertEquals(IPv4::create('192.168.100.15/4')->broadcast(), 3489660927); // 207.255.255.255
    }

    public function testNumAddrs()
    {
        $this->assertEquals(IPv4::create('0/0')->numAddrs(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('255.255.255.255/32')->numAddrs(), 1);
        $this->assertEquals(IPv4::create('192.168/16')->numAddrs(), 0x00010000);
        $this->assertEquals(IPv4::create('192.168.100.15/30')->numAddrs(), 4);
        $this->assertEquals(IPv4::create('192.168.100.15/4')->numAddrs(), 0x10000000);
    }

    public function testNumHosts()
    {
        $this->assertEquals(IPv4::create('0/0')->numHosts(), 0xFFFFFFFD);
        $this->assertEquals(IPv4::create('255.255.255.255/32')->numHosts(), 1);
        $this->assertEquals(IPv4::create('192.168/16')->numHosts(), 0x0000FFFE);
        $this->assertEquals(IPv4::create('192.168.100.15/30')->numHosts(), 2);
        $this->assertEquals(IPv4::create('192.168.100.15/4')->numHosts(), 0x0FFFFFFE);
    }

    public function testHostBits()
    {
        $this->assertEquals(IPv4::create(0)->hostBits(), 0);
        $this->assertEquals(IPv4::create('255.255.255.255', '255.255.255.255')->hostBits(), 0);
        $this->assertEquals(IPv4::create('192.168/16')->hostBits(), 16);
        $this->assertEquals(IPv4::create('192.168.100.15/30')->hostBits(), 2);
        $this->assertEquals(IPv4::create('10/8')->hostBits(), 24);
    }

    public function testCidr()
    {
        $this->assertEquals(IPv4::create(0)->cidr(), '0.0.0.0/32');
        $this->assertEquals(IPv4::create('255.255.255.255', '255.255.255.255')->cidr(), '255.255.255.255/32');
        $this->assertEquals(IPv4::create('192.168/16')->cidr(), '192.168.0.0/16');
        $this->assertEquals(IPv4::create('192.168.100.15', '255.255.255.252')->cidr(), '192.168.100.15/30');
        $this->assertEquals(IPv4::create('10/8')->cidr(), '10.0.0.0/8');
    }

    public function testReverse()
    {
        $this->assertEquals(IPv4::create(0)->reverse(), '0.0.0.0.in-addr.arpa');
        $this->assertEquals(IPv4::create('255.255.255.255', '255.255.255.255')->reverse(), '255.255.255.255.in-addr.arpa');
        $this->assertEquals(IPv4::create('192.168/16')->reverse(), '0.0.168.192.in-addr.arpa');
        $this->assertEquals(IPv4::create('192.168.100.15', '255.255.255.252')->reverse(), '15.100.168.192.in-addr.arpa');
        $this->assertEquals(IPv4::create('10/8')->reverse(), '0.0.0.10.in-addr.arpa');
    }

    public function testNetType()
    {
        $this->assertEquals(IPv4::create()->netType(), '"This" Network');
        $this->assertEquals(IPv4::create('192.168')->netType(), 'Private-Use Networks');
        $this->assertEquals(IPv4::create('224.0.0.10')->netType(), 'Multicast');
        $this->assertEquals(IPv4::create('95.126.18.4')->netType(), 'Public');
    }

    public function testNetClass()
    {
        $this->assertEquals(IPv4::create()->netClass(), 'C');
        $this->assertEquals(IPv4::create('192.168/16')->netClass(), 'E');
        $this->assertEquals(IPv4::create('192.169/16')->netClass(), 'B');
        $this->assertEquals(IPv4::create('127/8')->netClass(), 'A');
        $this->assertEquals(IPv4::create('224/4')->netClass(), 'D');
        $this->assertEquals(IPv4::create('95.126.18.4/24')->netClass(), 'C');
        $this->assertEquals(IPv4::create('1.2.3.4/4')->netClass(), '-');
    }

    public function testMatch()
    {
        $this->assertTrue(IPv4::create()->match(IPv4::create()));
        $this->assertTrue(IPv4::create('255.255.255.255')->match(IPv4::create(0xFFFFFFFF)));
        $this->assertTrue(IPv4::create('192.168.100.100')->match(IPv4::create('192.168/16')));
        $this->assertFalse(IPv4::create('192.167.100.100')->match(IPv4::create('192.168/16')));
    }

    public function testToString()
    {
        $ip = new IPv4('10.10.10.3', '255.255.255.252');

        $this->assertEquals(sprintf("%s", $ip), '10.10.10.3/30');

        $ip->assign('192.168', '255.255.255');

        $this->assertEquals(sprintf("%s", $ip), '192.168.0.0/24');
    }
}