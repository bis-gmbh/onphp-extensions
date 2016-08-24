<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

use \Onphp\Extensions\Mail\MIMEDecode;

class MIMEDecodeTest extends PHPUnit_Framework_TestCase
{
    protected $letter1;

    protected function setUp()
    {
        $this->letter1 = file_get_contents('./tests/Mail/letter1.txt');
    }

    public function testDecodeLetter1()
    {
        $decoder = new MIMEDecode($this->letter1);
        $msg = $decoder->decode();

        $this->assertTrue(is_array($msg->headers));
        $this->assertTrue(is_array($msg->ctype_parameters));
        $this->assertEquals($msg->ctype_parameters['charset'], 'UTF-8');

        $this->assertEquals($msg->ctype_primary, 'text');
        $this->assertEquals($msg->ctype_secondary, 'html');

        $this->assertTrue(is_array($msg->headers['received']));
        $this->assertTrue(is_array($msg->headers['return-path']));

        $this->assertEquals($msg->headers['from'], '"SPAM" <jodi@lula.rsf7.com>');
        $this->assertEquals($msg->headers['to'], '"mailbox" <mailbox@example.net>');
        $this->assertEmpty($msg->headers['subject']);
    }
}
