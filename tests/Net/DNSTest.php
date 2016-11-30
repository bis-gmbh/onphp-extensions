<?php

use \Onphp\Extensions\Net\DNS;

class DNSTest extends PHPUnit_Framework_TestCase
{
    public function testGethostbyaddrDefaultDNS()
    {
        $host = DNS::gethostbyaddr('178.63.151.224');
        $this->assertEquals($host, '2ip.ru');
    }

    public function testGethostbyaddrCustomDNS()
    {
        $host = DNS::gethostbyaddr('178.63.151.224', ['dns' => [ // https://dns.yandex.ru/
            '77.88.8.8',
            '77.88.8.1',
        ]]);
        $this->assertEquals($host, '2ip.ru');
    }
}
