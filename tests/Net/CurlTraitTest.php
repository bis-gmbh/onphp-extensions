<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Tests\Net;

use \PHPUnit_Framework_TestCase;
use \Onphp\Extensions\Net\OAuth2\YAAuthenticator;

class CurlTraitTest extends PHPUnit_Framework_TestCase
{
	public function testGetCurlOptions()
	{
		$curloptTimeout = 5;
		$curloptNewConnectTimeout = 10;
		$curloptUrl = 'http://example.net/';

		$defaultOptions = [
			CURLOPT_POST            => 1,
			CURLOPT_HEADER          => 0,
			CURLOPT_CONNECTTIMEOUT  => 1,
			CURLOPT_FRESH_CONNECT   => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FORBID_REUSE    => 1,
			CURLOPT_TIMEOUT         => $curloptTimeout,
			CURLOPT_SSL_VERIFYPEER  => false,
		];
		$countDefaultOptions = count($defaultOptions);

		$yaAuth = new YAAuthenticator([
			'yandexId'       => '0123456789',
			'yandexPassword' => '0123456789',
		]);

		//---------------------
		$yaAuth->curlOptions([
			CURLOPT_CONNECTTIMEOUT  => $curloptNewConnectTimeout,
		]);

		$checkOptions = $yaAuth->getCurlOptions($defaultOptions);

		$this->assertEquals(count($checkOptions), $countDefaultOptions); // check number of options
		$this->assertEquals($checkOptions[CURLOPT_CONNECTTIMEOUT], $curloptNewConnectTimeout); // check changed option
		$this->assertEquals($checkOptions[CURLOPT_TIMEOUT], $curloptTimeout); // check unchanged option

		//----------------------
		$yaAuth->curlOptions([
			CURLOPT_URL  => $curloptUrl,
		]);

		$checkOptions = $yaAuth->getCurlOptions($defaultOptions);

		$this->assertEquals(count($checkOptions), $countDefaultOptions + 1); // check new options exists
	}
}
