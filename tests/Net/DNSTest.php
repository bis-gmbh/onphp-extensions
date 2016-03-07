<?php

use \Onphp\Extensions\Net\DNS;

class DNSTest extends PHPUnit_Framework_TestCase
{
    const DNS_SERVER_IP = '8.8.4.4';

    public function testGethostbyaddr()
    {
        $host = DNS::gethostbyaddr('178.63.151.224', ['dns' => self::DNS_SERVER_IP]);
        $this->assertEquals($host, '2ip.ru');
    }
}